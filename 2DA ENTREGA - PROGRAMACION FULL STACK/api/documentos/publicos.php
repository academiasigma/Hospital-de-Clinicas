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

$ciPaciente = trim($_GET['ci'] ?? '');
$modelo = new Documento();
$documentos = $modelo->listarPublicos();

$pacienteValido = false;
$nombrePaciente = '';

// Si ingresó cédula, verificar la existencia en la tabla de pacientes 
if (!empty($ciPaciente)) {
    $db = (new Database())->conectar();
    $sqlPac = "SELECT nombre FROM paciente WHERE documento = :ci LIMIT 1";
    $stmtPac = $db->prepare($sqlPac);
    $stmtPac->bindParam(':ci', $ciPaciente, PDO::PARAM_STR);
    $stmtPac->execute();
    $paciente = $stmtPac->fetch();

    if ($paciente) {
        $pacienteValido = true;
        $nombrePaciente = $paciente['nombre'];
    } else {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'mensaje' => 'La cédula ingresada no se encuentra registrada como paciente activo.']);
        exit;
    }
}

// Formatear listado aplicando un ocultamiento si no hay C.I. válida
$datosFormateados = [];
foreach ($documentos as $doc) {
    $esSensible = (int)$doc['es_sensible'] === 1;
    $bloqueado = $esSensible && !$pacienteValido;

    $datosFormateados[] = [
        'id_documento'     => (int)$doc['id_documento'],
        'titulo'           => $doc['titulo'],
        'categoria_nombre' => $doc['categoria_nombre'],
        'id_categoria'     => (int)$doc['id_categoria'],
        'es_sensible'      => $esSensible,
        'bloqueado'        => $bloqueado,
        'archivo'          => $bloqueado ? null : $doc['archivo'], //Bloqueo de URL
        'codigo_qr'        => $doc['codigo_qr']
    ];
}

http_response_code(200);
echo json_encode([
    'status'          => 'ok',
    'pacienteValido'  => $pacienteValido,
    'nombrePaciente'  => $nombrePaciente,
    'documentos'      => $datosFormateados
]);
