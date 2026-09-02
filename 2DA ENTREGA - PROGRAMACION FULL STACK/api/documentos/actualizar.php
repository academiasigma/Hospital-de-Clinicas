<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__ . '/../../backend/config/Auth.php';
require_once __DIR__ . '/../../backend/models/Documento.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'mensaje' => 'Método no permitido.']);
    exit;
}

if (!Auth::verificarAutenticacion()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'mensaje' => 'Sesión requerida.']);
    exit;
}

$id_documento = isset($_POST['id_documento']) ? (int)$_POST['id_documento'] : 0;
$titulo = trim($_POST['titulo'] ?? '');
$id_categoria = isset($_POST['id_categoria']) ? (int)$_POST['id_categoria'] : 0;


if ($id_documento <= 0 || empty($titulo) || $id_categoria <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'mensaje' => 'Faltan datos obligatorios para actualizar.']);
    exit;
}

$modelo = new Documento();
$datosActualizar = [
    'titulo'       => $titulo,
    'id_categoria' => $id_categoria,
    
    'archivo'      => null
];

if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
    $archivoTmp = $_FILES['archivo']['tmp_name'];
    $nombreOriginal = $_FILES['archivo']['name'];
    $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

    $extensionesPermitidas = ['pdf', 'jpg', 'jpeg', 'png'];
    if (!in_array($extension, $extensionesPermitidas, true)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'mensaje' => 'Formato de archivo no válido. Solo PDF, JPG o PNG.']);
        exit;
    }

    $carpetaDestino = __DIR__ . '/../../uploads/documentos/';
    $nuevoNombre = 'doc_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $rutaFinal = $carpetaDestino . $nuevoNombre;

    if (!move_uploaded_file($archivoTmp, $rutaFinal)) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'mensaje' => 'No se pudo guardar el nuevo archivo.']);
        exit;
    }

    $datosActualizar['archivo'] = 'uploads/documentos/' . $nuevoNombre;
}

$exito = $modelo->actualizar($id_documento, $datosActualizar);

if (!$exito) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'mensaje' => 'No se pudo actualizar el registro en la base de datos.']);
    exit;
}

http_response_code(200);
echo json_encode(['status' => 'ok', 'mensaje' => 'Documento modificado correctamente.']);
