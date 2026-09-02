<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../backend/config/Auth.php';
require_once __DIR__ . '/../../backend/models/Usuario.php';

Auth::iniciarSesion();

if (!Auth::verificarAutenticacion()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'mensaje' => 'No autorizado.']);
    exit;
}

if (!Auth::verificarRol(['SUPERADMIN_IT'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'mensaje' => 'Acceso denegado. Solo el Administrador TI puede eliminar usuarios.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'mensaje' => 'Método no permitido.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id_usuario = isset($input['id_usuario']) ? (int)$input['id_usuario'] : 0;

if ($id_usuario <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'mensaje' => 'ID de usuario no válido.']);
    exit;
}

// Prevenir que el usuario se elimine a el mismo
if ($id_usuario === (int)$_SESSION['usuario']['id_usuario']) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'mensaje' => 'No puede eliminar su propia cuenta activa.']);
    exit;
}

$modelo = new Usuario();
$eliminado = $modelo->eliminar($id_usuario);

if (!$eliminado) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'mensaje' => 'Error al eliminar el usuario del sistema.']);
    exit;
}

http_response_code(200);
echo json_encode(['status' => 'ok', 'mensaje' => 'Usuario eliminado de forma definitiva.']);
