<?php

require_once __DIR__ . '/../../backend/config/Api.php';
require_once __DIR__ . '/../../backend/models/Traslado.php';
require_once __DIR__ . '/../../backend/models/Paciente.php';

// api para listar, crear, editar y cambiar el estado de los traslados
const ROLES_TRANSPORTE = ['SUPERADMIN_IT', 'GESTOR_INTEGRAL'];

$metodo = Api::iniciar(['GET', 'POST', 'PUT', 'DELETE']);
$id = Api::idDeLaUrl();
$accion = $_GET['accion'] ?? '';
$traslado = new Traslado();

// devuelve los errores que encontro el modelo
$responderErrorModelo = function () use ($traslado) {
    Api::error($traslado->codigoError, $traslado->errores[0] ?? 'Error al procesar el traslado.', $traslado->errores);
};

if ($metodo === 'GET') {
    Api::exigirRol(['SUPERADMIN_IT', 'GESTOR_INTEGRAL', 'DIRECTOR_GENERAL', 'USUARIO_DIVISION']);
    $rol = $_SESSION['usuario']['rol'];
    // si es un usuario de division solo ve los traslados que pidio el
    $soloPropios = ($rol === 'USUARIO_DIVISION') ? (int)$_SESSION['usuario']['id_usuario'] : null;

    if ($id !== null) {
        $fila = $traslado->obtenerPorId($id);
        if ($fila === null || ($soloPropios !== null && (int)$fila['id_usuario'] !== $soloPropios)) {
            Api::error(404, 'Traslado no encontrado.');
        }
        Api::responder(200, ['status' => 'ok', 'datos' => $fila]);
    }

    $filas = $traslado->listar($_GET, $soloPropios);
    Api::responder(200, ['status' => 'ok', 'total' => count($filas), 'datos' => $filas]);
}

if ($metodo === 'POST') {
    Api::exigirRol(['SUPERADMIN_IT', 'GESTOR_INTEGRAL', 'USUARIO_DIVISION']);
    $datos = Api::entrada();
    $esDivision = ($_SESSION['usuario']['rol'] === 'USUARIO_DIVISION');

    // si vienen los datos del paciente lo registramos o actualizamos por su cedula
    if (isset($datos['paciente']) && is_array($datos['paciente'])) {
        $modeloPaciente = new Paciente();
        $erroresPaciente = $modeloPaciente->validar($datos['paciente']);
        if (!empty($erroresPaciente)) {
            Api::error(400, 'Datos del paciente inválidos.', $erroresPaciente);
        }
        $idPaciente = $modeloPaciente->crear($datos['paciente']);
        if ($idPaciente === null) {
            Api::error(500, 'No se pudo registrar al paciente.');
        }
        $datos['id_paciente'] = $idPaciente;
    }

    $nuevoId = $traslado->crear($datos, (int)$_SESSION['usuario']['id_usuario'], $esDivision);
    if ($nuevoId === null) {
        $responderErrorModelo();
    }
    Api::responder(201, ['status' => 'ok', 'mensaje' => 'Traslado registrado.', 'id' => $nuevoId]);
}

// editar y cancelar solo lo pueden hacer transporte y admin
Api::exigirRol(ROLES_TRANSPORTE);
if ($id === null) {
    Api::error(400, 'Debe indicar el id del traslado en la URL.');
}

if ($metodo === 'PUT' && $accion === 'estado') {
    $datos = Api::entrada();
    $nuevoEstado = trim((string)($datos['estado'] ?? ''));
    if ($nuevoEstado === '') {
        Api::error(400, 'El campo estado es obligatorio.');
    }
    $ok = $traslado->cambiarEstado($id, $nuevoEstado, $datos['hora_efectiva_llegada'] ?? null, $datos['hora_retorno'] ?? null);
    if (!$ok) {
        $responderErrorModelo();
    }
    Api::responder(200, ['status' => 'ok', 'mensaje' => "Estado actualizado a $nuevoEstado."]);
}

if ($metodo === 'PUT') {
    if (!$traslado->actualizar($id, Api::entrada())) {
        $responderErrorModelo();
    }
    Api::responder(200, ['status' => 'ok', 'mensaje' => 'Traslado actualizado.']);
}

// cancelar el traslado para que quede guardado en el historial
if (!$traslado->cancelar($id)) {
    $responderErrorModelo();
}
Api::responder(200, ['status' => 'ok', 'mensaje' => 'Traslado cancelado.']);
