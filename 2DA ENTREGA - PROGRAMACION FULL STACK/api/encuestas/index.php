<?php
// api/encuestas/index.php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../backend/models/Encuesta.php';

$metodo = $_SERVER['REQUEST_METHOD'];
$modelo = new Encuesta();

// -------------------------------------------------------------
// GET: Obtener encuesta activa para una categoría
// -------------------------------------------------------------
if ($metodo === 'GET') {
    $id_categoria = isset($_GET['id_categoria']) ? (int)$_GET['id_categoria'] : 0;

    if ($id_categoria <= 0) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'mensaje' => 'ID de categoría no válido.']);
        exit;
    }

    $encuesta = $modelo->obtenerActivaPorCategoria($id_categoria);

    if (!$encuesta) {
        http_response_code(200);
        echo json_encode(['status' => 'ok', 'encuesta' => null]);
        exit;
    }

    http_response_code(200);
    echo json_encode(['status' => 'ok', 'encuesta' => $encuesta]);
    exit;
}

// -------------------------------------------------------------
// POST: Enviar resolución de encuesta (anónimo)
// -------------------------------------------------------------
if ($metodo === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $id_encuesta = isset($input['id_encuesta']) ? (int)$input['id_encuesta'] : 0;
    $observaciones = trim($input['observaciones'] ?? '');
    $respuestas = $input['respuestas'] ?? [];

    if ($id_encuesta <= 0) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'mensaje' => 'ID de encuesta no válido o ausente.']);
        exit;
    }

    if (empty($respuestas) || !is_array($respuestas)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'mensaje' => 'El cuerpo de respuestas es obligatorio.']);
        exit;
    }

    $guardado = $modelo->guardarResolucion($id_encuesta, $observaciones, $respuestas);

    if (!$guardado) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'mensaje' => 'Ocurrió un error en el servidor al almacenar sus respuestas.']);
        exit;
    }

    http_response_code(201);
    echo json_encode(['status' => 'ok', 'mensaje' => '¡Muchas gracias por completar la encuesta! Sus respuestas han sido guardadas de forma anónima.']);
    exit;
}

http_response_code(405);
echo json_encode(['status' => 'error', 'mensaje' => 'Método de petición no permitido.']);
