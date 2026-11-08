<?php

require_once __DIR__ . '/../../backend/config/Api.php';
require_once __DIR__ . '/../../backend/models/Documento.php';

/**
 * Endpoint REST de documentos clínicos (/api/documentos y /api/documentos/{id}):
 *   GET    /api/documentos[?id_categoria=] -> listado
 *   GET    /api/documentos/{id}            -> detalle de un documento
 *   POST   /api/documentos                 -> alta (multipart/form-data con archivo, o reemplazo de archivo si viene _method=PUT)
 *   PUT    /api/documentos/{id}            -> edición de metadatos (application/json: titulo, id_categoria)
 *   DELETE /api/documentos/{id}            -> eliminación física y lógica
 *
 * Siguiendo el estándar de Ejemplo2 de clase, se valida el tamaño máximo (10 MB),
 * el tipo MIME real con finfo_file() y la firma del archivo (%PDF- en caso de PDF).
 */
header('X-Content-Type-Options: nosniff');

$metodo = Api::iniciar(['GET', 'POST', 'PUT', 'DELETE']);
$id = Api::idDeLaUrl();
$modelo = new Documento();
$tamanoMaximo = 10 * 1024 * 1024; // 10 MB

// Auxiliar para validar y guardar un archivo subido en $_FILES['archivo']
$procesarArchivoSubido = function (array $archivo) use ($tamanoMaximo): string {
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        Api::error(400, 'Debe adjuntar un archivo válido (PDF o Imagen).');
    }
    if (!is_uploaded_file($archivo['tmp_name']) || $archivo['size'] <= 0) {
        Api::error(400, 'El archivo recibido no es válido.');
    }
    if ($archivo['size'] > $tamanoMaximo) {
        Api::error(400, 'El archivo supera el límite permitido de 10 MB.');
    }

    $nombreOriginal = basename(str_replace('\\', '/', $archivo['name']));
    $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
    $extensionesPermitidas = ['pdf', 'jpg', 'jpeg', 'png'];
    if (!in_array($extension, $extensionesPermitidas, true)) {
        Api::error(400, 'Formato no permitido. Solo se aceptan PDF, JPG o PNG.');
    }

    // Validación de tipo MIME real e inspección de firma binaria (como en Ejemplo2 de clase)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $tipoMime = finfo_file($finfo, $archivo['tmp_name']);
    finfo_close($finfo);

    $mimesPermitidos = ['application/pdf', 'image/jpeg', 'image/png'];
    if (!in_array($tipoMime, $mimesPermitidos, true)) {
        Api::error(400, 'El contenido real del archivo no corresponde a un PDF o imagen válida.');
    }
    if ($extension === 'pdf') {
        $handle = fopen($archivo['tmp_name'], 'rb');
        $cabecera = fread($handle, 5);
        fclose($handle);
        if ($cabecera !== '%PDF-') {
            Api::error(400, 'El archivo debe ser un PDF válido.');
        }
    }

    $carpetaDestino = __DIR__ . '/../../uploads/documentos/';
    if (!is_dir($carpetaDestino) && !mkdir($carpetaDestino, 0775, true) && !is_dir($carpetaDestino)) {
        Api::error(500, 'No se pudo crear la carpeta de destino en el servidor.');
    }

    $nombreArchivoGuardado = 'doc_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $rutaFinalServidor = $carpetaDestino . $nombreArchivoGuardado;

    if (!move_uploaded_file($archivo['tmp_name'], $rutaFinalServidor)) {
        Api::error(500, 'Fallo al guardar el archivo en el servidor.');
    }

    return 'uploads/documentos/' . $nombreArchivoGuardado;
};

// GET: listado general o detalle por ID
if ($metodo === 'GET') {
    if ($id !== null) {
        $doc = $modelo->obtenerPorId($id);
        if ($doc === null) {
            Api::error(404, 'Documento no encontrado.');
        }
        Api::responder(200, ['status' => 'ok', 'datos' => $doc]);
    }

    $idCategoria = isset($_GET['id_categoria']) && is_numeric($_GET['id_categoria'])
        ? (int)$_GET['id_categoria']
        : null;

    $documentos = $modelo->listarTodos($idCategoria);
    Api::responder(200, [
        'status' => 'ok',
        'total'  => count($documentos),
        'datos'  => $documentos
    ]);
}

// Operaciones de escritura requieren sesión activa y rol con permisos de gestión documental
Api::exigirRol(['SUPERADMIN_IT', 'GESTOR_INTEGRAL', 'USUARIO_DIVISION']);

// Si es POST pero incluye id en la URL o _method=PUT (usado cuando se reemplaza el archivo adjunto por multipart/form-data)
if ($metodo === 'POST' && ($id !== null || strtoupper($_POST['_method'] ?? '') === 'PUT' || !empty($_POST['id_documento']))) {
    $idEditar = $id ?? (int)($_POST['id_documento'] ?? 0);
    if ($idEditar <= 0 || $modelo->obtenerPorId($idEditar) === null) {
        Api::error(404, 'Documento no encontrado.');
    }

    $titulo = trim($_POST['titulo'] ?? '');
    $id_categoria = isset($_POST['id_categoria']) ? (int)$_POST['id_categoria'] : 0;
    if ($titulo === '' || mb_strlen($titulo) > 150 || $id_categoria <= 0) {
        Api::error(400, 'El título (máx. 150 caracteres) y la categoría son obligatorios.');
    }

    $rutaBD = null;
    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $rutaBD = $procesarArchivoSubido($_FILES['archivo']);
    }

    if (!$modelo->actualizar($idEditar, [
        'titulo'       => $titulo,
        'id_categoria' => $id_categoria,
        'archivo'      => $rutaBD
    ])) {
        Api::error(500, 'No se pudo actualizar el registro en la base de datos.');
    }

    Api::responder(200, ['status' => 'ok', 'mensaje' => 'Documento modificado correctamente.']);
}

// POST: Alta de nuevo documento
if ($metodo === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $id_categoria = isset($_POST['id_categoria']) ? (int)$_POST['id_categoria'] : 0;
    $id_usuario = (int)$_SESSION['usuario']['id_usuario'];

    if ($titulo === '' || mb_strlen($titulo) > 150 || $id_categoria <= 0) {
        Api::error(400, 'El título (máx. 150 caracteres) y la categoría son obligatorios.');
    }
    if (!isset($_FILES['archivo'])) {
        Api::error(400, 'Debe adjuntar un archivo válido (PDF o Imagen).');
    }

    $rutaBD = $procesarArchivoSubido($_FILES['archivo']);

    try {
        $exito = $modelo->crear([
            'titulo'       => $titulo,
            'archivo'      => $rutaBD,
            'id_usuario'   => $id_usuario,
            'id_categoria' => $id_categoria
        ]);
    } catch (Throwable $e) {
        $exito = false;
    }

    if (!$exito) {
        $rutaFisica = __DIR__ . '/../../' . $rutaBD;
        if (is_file($rutaFisica)) {
            unlink($rutaFisica);
        }
        Api::error(500, 'Error al registrar el documento en la base de datos.');
    }

    Api::responder(201, ['status' => 'ok', 'mensaje' => 'Documento registrado exitosamente.']);
}

// PUT: Actualizar metadatos del documento (/api/documentos/{id}) con cuerpo JSON
if ($metodo === 'PUT') {
    $datos = Api::entrada();
    $idEditar = $id ?? (int)($datos['id_documento'] ?? 0);
    if ($idEditar <= 0) {
        Api::error(400, 'Debe indicar el ID del documento a actualizar.');
    }
    if ($modelo->obtenerPorId($idEditar) === null) {
        Api::error(404, 'Documento no encontrado.');
    }

    $titulo = trim((string)($datos['titulo'] ?? ''));
    $id_categoria = (int)($datos['id_categoria'] ?? 0);

    if ($titulo === '' || mb_strlen($titulo) > 150 || $id_categoria <= 0) {
        Api::error(400, 'El título (máx. 150 caracteres) y la categoría son obligatorios.');
    }

    $exito = $modelo->actualizar($idEditar, [
        'titulo'       => $titulo,
        'id_categoria' => $id_categoria,
        'archivo'      => null
    ]);

    if (!$exito) {
        Api::error(500, 'No se pudo actualizar el documento.');
    }

    Api::responder(200, ['status' => 'ok', 'mensaje' => 'Documento modificado correctamente.']);
}

// DELETE: Eliminar documento (/api/documentos/{id})
if ($metodo === 'DELETE') {
    $idEliminar = $id;
    if ($idEliminar === null) {
        $datos = Api::entrada();
        $idEliminar = isset($datos['id_documento']) ? (int)$datos['id_documento'] : null;
    }
    if ($idEliminar === null || $idEliminar <= 0) {
        Api::error(400, 'Debe indicar el ID del documento a eliminar.');
    }
    if ($modelo->obtenerPorId($idEliminar) === null) {
        Api::error(404, 'Documento no encontrado.');
    }

    if (!$modelo->eliminar($idEliminar)) {
        Api::error(500, 'No se pudo eliminar el documento.');
    }

    Api::responder(200, ['status' => 'ok', 'mensaje' => 'Documento eliminado exitosamente.']);
}
