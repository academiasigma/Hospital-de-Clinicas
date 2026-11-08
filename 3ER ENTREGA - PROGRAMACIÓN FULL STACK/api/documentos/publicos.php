<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once __DIR__ . '/../../backend/config/Database.php';
require_once __DIR__ . '/../../backend/models/Documento.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'mensaje' => 'Método no permitido.']);
    exit;
}

$modelo = new Documento();
$documentos = $modelo->listarPublicos();


$datosFormateados = [];
foreach ($documentos as $doc) {
    

    $datosFormateados[] = [
        'id_documento'     => (int)$doc['id_documento'],
        'titulo'           => $doc['titulo'],
        'categoria_nombre' => $doc['categoria_nombre'],
        'id_categoria'     => (int)$doc['id_categoria'],
        'archivo'          => $doc['archivo'] //Acceso directo al doc
    ];
}

http_response_code(200);
echo json_encode([
    'status'          => 'ok',
    'documentos'      => $datosFormateados

]);
