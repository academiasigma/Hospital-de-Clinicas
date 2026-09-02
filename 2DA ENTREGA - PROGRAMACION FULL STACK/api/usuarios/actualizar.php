<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../backend/config/Auth.php';
require_once __DIR__ . '/../../backend/models/Usuario.php';

Auth::iniciarSesion();

// Verificar autenticación
if (!Auth::verificarAutenticacion()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'mensaje' => 'No autorizado. Debe iniciar sesión.']);
    exit;
}

// Solo SUPERADMIN_IT puede actualizar los usuarios
if (!Auth::verificarRol(['SUPERADMIN_IT'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'mensaje' => 'Privilegios insuficientes. Solo el Administrador TI puede modificar usuarios.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'mensaje' => 'Método no permitido. Utilice POST.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$id_usuario = isset($input['id_usuario']) ? (int)$input['id_usuario'] : 0;
$email = trim($input['email'] ?? '');
$nombre = trim($input['nombre'] ?? '');
$contrasenia = trim($input['contrasenia'] ?? '');
$rol = trim($input['rol'] ?? '');
$estado = trim($input['estado'] ?? '');

if ($id_usuario <= 0 || empty($email) || empty($nombre) || empty($rol) || empty($estado)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'mensaje' => 'Faltan datos obligatorios para actualizar el usuario.']);
    exit;
}

// Validar el formato del mail
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'mensaje' => 'El formato del correo electrónico es inválido.']);
    exit;
}

$modelo = new Usuario();

// Verificar que el correo no esté ocupado por otro usuario
$usuarioExistente = $modelo->obtenerPorEmail($email);
if ($usuarioExistente !== null && (int)$usuarioExistente['id_usuario'] !== $id_usuario) {
    http_response_code(409);
    echo json_encode(['status' => 'error', 'mensaje' => 'El correo electrónico ya se encuentra registrado por otro funcionario.']);
    exit;
}

// No dejar que el superadmin se autodesactive o que autocambie el rol si es su propio usuario
$usuarioActualSesion = $_SESSION['usuario'];
if ((int)$usuarioActualSesion['id_usuario'] === $id_usuario) {
    if ($rol !== 'SUPERADMIN_IT' || $estado !== 'Activo') {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'mensaje' => 'No puedes cambiar tu propio nivel de acceso ni suspender tu cuenta activa.']);
        exit;
    }
}

$datosActualizar = [
    'email' => $email,
    'nombre' => $nombre,
    'rol' => $rol,
    'estado' => $estado,
    'contrasenia' => !empty($contrasenia) ? $contrasenia : null
];

$actualizado = $modelo->actualizar($id_usuario, $datosActualizar);

if (!$actualizado) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'mensaje' => 'Ocurrió un error en el servidor al intentar actualizar el funcionario.']);
    exit;
}

http_response_code(200);
echo json_encode(['status' => 'ok', 'mensaje' => 'Funcionario actualizado con éxito.']);
exit;
