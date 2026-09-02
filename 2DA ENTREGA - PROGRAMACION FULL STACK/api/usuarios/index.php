<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
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

$metodo = $_SERVER['REQUEST_METHOD'];
$modelo = new Usuario();


// GET: Obtener todos los usuarios registrados

if ($metodo === 'GET') {
    $usuarios = $modelo->listarTodos();
    
    // Formatear la lista para no exponer las contraseñas
    $datosFormateados = [];
    foreach ($usuarios as $usr) {
        $datosFormateados[] = [
            'id_usuario' => (int)$usr['id_usuario'],
            'email'      => $usr['email'],
            'nombre'     => $usr['nombre'],
            'rol'        => $usr['rol'],
            'estado'     => $usr['estado']
        ];
    }

    http_response_code(200);
    echo json_encode(['status' => 'ok', 'usuarios' => $datosFormateados]);
    exit;
}

// POST: Registrar un nuevo usuario funcionario

if ($metodo === 'POST') {
    //Solo el SUPERADMIN_IT puede registrar usuarios
    if (!Auth::verificarRol(['SUPERADMIN_IT'])) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'mensaje' => 'Privilegios insuficientes. Solo el Administrador TI puede crear usuarios.']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    
    $email = trim($input['email'] ?? '');
    $nombre = trim($input['nombre'] ?? '');
    $contrasenia = trim($input['contrasenia'] ?? '');
    $rol = trim($input['rol'] ?? '');
    $estado = trim($input['estado'] ?? 'Activo');

    if (empty($email) || empty($nombre) || empty($contrasenia) || empty($rol)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'mensaje' => 'Todos los campos son obligatorios (Nombre, Email, Contraseña, Rol).']);
        exit;
    }

    // Validar formato de email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'mensaje' => 'El formato del correo electrónico es inválido.']);
        exit;
    }

    // Verificar si el correo ya existe
    if ($modelo->obtenerPorEmail($email) !== null) {
        http_response_code(409);
        echo json_encode(['status' => 'error', 'mensaje' => 'El correo electrónico ya se encuentra registrado.']);
        exit;
    }

    // Crear el usuario
    $datosNuevos = [
        'email' => $email,
        'nombre' => $nombre,
        'contrasenia' => $contrasenia,
        'rol' => $rol,
        'estado' => $estado
    ];

    $creado = $modelo->crear($datosNuevos);

    if (!$creado) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'mensaje' => 'Error en el servidor al registrar el usuario.']);
        exit;
    }

    http_response_code(201);
    echo json_encode(['status' => 'ok', 'mensaje' => 'Funcionario registrado exitosamente.']);
    exit;
}

http_response_code(405);
echo json_encode(['status' => 'error', 'mensaje' => 'Método no permitido.']);
