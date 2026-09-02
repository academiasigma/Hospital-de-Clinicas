<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once __DIR__ . '/../backend/config/Auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'mensaje' => 'Método no permitido.']);
    exit;
}

if (!Auth::verificarAutenticacion()) {
    http_response_code(401);
    echo json_encode([
        'status'        => 'error',
        'autenticado'   => false,
        'mensaje'       => 'No hay sesión activa.'
    ]);
    exit;
}

http_response_code(200);
echo json_encode([
    'status'      => 'ok',
    'autenticado' => true,
    'usuario'     => $_SESSION['usuario']
]);
