<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

require_once __DIR__ . '/../../backend/config/Auth.php';
require_once __DIR__ . '/../../backend/models/Documento.php';

$metodo = $_SERVER['REQUEST_METHOD'];
$modelo = new Documento();

if ($metodo === 'GET') {
    $idCategoria = isset($_GET['id_categoria']) && is_numeric($_GET['id_categoria']) 
        ? (int)$_GET['id_categoria'] 
        : null;

    $documentos = $modelo->listarTodos($idCategoria);

    http_response_code(200);
    echo json_encode([
        'status' => 'ok',
        'total'  => count($documentos),
        'datos'  => $documentos
    ]);
    exit;
}

if ($metodo === 'POST') {
    if (!Auth::verificarAutenticacion()) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'mensaje' => 'Sesión requerida para cargar documentos.']);
        exit;
    }

    $titulo = trim($_POST['titulo'] ?? '');
    $id_categoria = isset($_POST['id_categoria']) ? (int)$_POST['id_categoria'] : 0;
    $es_sensible = isset($_POST['es_sensible']) ? (int)$_POST['es_sensible'] : 0;
    $id_usuario = (int)$_SESSION['usuario']['id_usuario'];

    if (empty($titulo) || $id_categoria <= 0) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'mensaje' => 'El título y la categoría son obligatorios.']);
        exit;
    }

    if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'mensaje' => 'Debe adjuntar un archivo válido (PDF o Imagen).']);
        exit;
    }

    $archivoTmp = $_FILES['archivo']['tmp_name'];
    $nombreOriginal = $_FILES['archivo']['name'];
    $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

    $extensionesPermitidas = ['pdf', 'jpg', 'jpeg', 'png'];
    if (!in_array($extension, $extensionesPermitidas, true)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'mensaje' => 'Formato no permitido. Solo se aceptan PDF, JPG o PNG.']);
        exit;
    }

    $carpetaDestino = __DIR__ . '/../../uploads/documentos/';
    if (!is_dir($carpetaDestino)) {
        mkdir($carpetaDestino, 0777, true);
    }

    $nombreArchivoGuardado = 'doc_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $rutaFinalServidor = $carpetaDestino . $nombreArchivoGuardado;
    $rutaBD = 'uploads/documentos/' . $nombreArchivoGuardado;

    if (!move_uploaded_file($archivoTmp, $rutaFinalServidor)) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'mensaje' => 'Fallo al guardar el archivo en el servidor.']);
        exit;
    }

    $codigoQR = 'QR_HC_' . strtoupper(bin2hex(random_bytes(8)));

    $exito = $modelo->crear([
        'titulo'       => $titulo,
        'archivo'      => $rutaBD,
        'codigo_qr'    => $codigoQR,
        'es_sensible'  => $es_sensible,
        'id_usuario'   => $id_usuario,
        'id_categoria' => $id_categoria
    ]);

    if (!$exito) {
        unlink($rutaFinalServidor);
        http_response_code(500);
        echo json_encode(['status' => 'error', 'mensaje' => 'Error al registrar el documento en la base de datos.']);
        exit;
    }

    http_response_code(201);
    echo json_encode([
        'status'    => 'ok',
        'mensaje'   => 'Documento registrado exitosamente.',
        'codigo_qr' => $codigoQR
    ]);
    exit;
}

http_response_code(405);
echo json_encode(['status' => 'error', 'mensaje' => 'Método no soportado.']);
