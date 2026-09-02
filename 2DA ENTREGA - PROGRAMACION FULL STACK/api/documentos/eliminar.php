<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, DELETE');

require_once __DIR__ . '/../../backend/config/Auth.php';
require_once __DIR__ . '/../../backend/models/Documento.php';

if (!Auth::verificarAutenticacion()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'mensaje' => 'Acceso denegado.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$id = (int)($_GET['id'] ?? $input['id_documento'] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'mensaje' => 'Identificador de documento no especificado.']);
    exit;
}

$modelo = new Documento();
$resultado = $modelo->eliminar($id);

if (!$resultado) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'mensaje' => 'No se encontró el documento o no pudo ser eliminado.']);
    exit;
}

http_response_code(200);
echo json_encode(['status' => 'ok', 'mensaje' => 'Documento eliminado exitosamente.']);
