<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../backend/config/Auth.php';
require_once __DIR__ . '/../backend/models/Usuario.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'mensaje' => 'Método no permitido. Utilice POST.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$email = trim($input['email'] ?? $_POST['email'] ?? '');
$password = trim($input['password'] ?? $_POST['password'] ?? '');

if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'mensaje' => 'El correo electrónico y la contraseña son obligatorios.']);
    exit;
}

$modeloUsuario = new Usuario();
$resultado = $modeloUsuario->autenticar($email, $password);

if (!$resultado['exito']) {
    http_response_code($resultado['codigo']);
    echo json_encode([
        'status'  => 'error',
        'mensaje' => $resultado['mensaje']
    ]);
    exit;
}

Auth::iniciarSesion();
$_SESSION['usuario'] = $resultado['usuario'];

http_response_code(200);
echo json_encode([
    'status'  => 'ok',
    'mensaje' => $resultado['mensaje'],
    'usuario' => $resultado['usuario']
]);
