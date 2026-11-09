<?php

require_once __DIR__ . '/../../backend/config/Api.php';
require_once __DIR__ . '/../../backend/models/Encuesta.php';

// api para encuestas, preguntas y respuestas de satisfaccion
$metodo = Api::iniciar(['GET', 'POST', 'PUT', 'DELETE']);
$id = Api::idDeLaUrl();
$recurso = $_GET['recurso'] ?? '';
$modelo = new Encuesta();

if ($metodo === 'GET') {
    // devuelve los resultados de las encuestas para los funcionarios logueados
    if (!empty($_GET['resultados']) || (($_GET['accion'] ?? '') === 'resultados')) {
        Api::exigirRol(['SUPERADMIN_IT', 'GESTOR_INTEGRAL', 'DIRECTOR_GENERAL', 'USUARIO_DIVISION']);
        $idCat = isset($_GET['id_categoria']) && is_numeric($_GET['id_categoria'])
            ? (int)$_GET['id_categoria']
            : null;
        $resultados = $modelo->obtenerResultados($idCat);
        Api::responder(200, [
            'status' => 'ok',
            'total'  => count($resultados),
            'datos'  => $resultados
        ]);
    }

    // lista todas las encuestas activas para el portal de pacientes
    if (!empty($_GET['activas'])) {
        $activas = $modelo->listarActivasPublicas();
        Api::responder(200, ['status' => 'ok', 'encuestas' => $activas]);
    }

    // busca una encuesta activa por su id
    if (isset($_GET['id_encuesta']) && is_numeric($_GET['id_encuesta'])) {
        $encuesta = $modelo->obtenerActivaPorId((int)$_GET['id_encuesta']);
        Api::responder(200, ['status' => 'ok', 'encuesta' => $encuesta]);
    }

    // busca la encuesta activa de una categoria
    $id_categoria = isset($_GET['id_categoria']) ? (int)$_GET['id_categoria'] : 0;
    if ($id_categoria <= 0) {
        Api::error(400, 'ID de categoría no válido.');
    }

    $encuesta = $modelo->obtenerActivaPorCategoria($id_categoria);
    Api::responder(200, ['status' => 'ok', 'encuesta' => $encuesta]);
}

if ($metodo === 'POST') {
    $input = Api::entrada();

    // agregar una pregunta nueva a una encuesta
    if ($recurso === 'pregunta' || ($input['accion'] ?? '') === 'crear_pregunta') {
        Api::exigirRol(['SUPERADMIN_IT', 'GESTOR_INTEGRAL']);
        $id_encuesta = (int)($input['id_encuesta'] ?? 0);
        $enunciado = (string)($input['enunciado'] ?? '');
        $tipo = (string)($input['tipo'] ?? 'Si/No');
        $opciones = is_array($input['opciones'] ?? null) ? $input['opciones'] : [];

        $nuevoId = $modelo->crearPregunta($id_encuesta, $enunciado, $tipo, $opciones);
        if ($nuevoId === null) {
            Api::error($modelo->codigoError, $modelo->ultimoError ?: 'No se pudo crear la pregunta.');
        }
        Api::responder(201, ['status' => 'ok', 'mensaje' => 'Pregunta agregada a la encuesta.', 'id' => $nuevoId]);
    }

    // crear una encuesta nueva para una categoria
    if ($recurso === 'encuesta' || ($input['accion'] ?? '') === 'crear_encuesta') {
        Api::exigirRol(['SUPERADMIN_IT', 'GESTOR_INTEGRAL']);
        $id_categoria = (int)($input['id_categoria'] ?? 0);
        $comentarios = (string)($input['comentarios'] ?? '');
        $estado = (string)($input['estado'] ?? 'Activa');

        $nuevoId = $modelo->crearEncuesta($id_categoria, $comentarios, $estado);
        if ($nuevoId === null) {
            Api::error($modelo->codigoError, $modelo->ultimoError ?: 'No se pudo crear la encuesta.');
        }
        Api::responder(201, ['status' => 'ok', 'mensaje' => 'Encuesta creada correctamente.', 'id' => $nuevoId]);
    }

    // guardar las respuestas que envia un paciente desde el portal
    $id_encuesta = isset($input['id_encuesta']) ? (int)$input['id_encuesta'] : 0;
    $observaciones = trim((string)($input['observaciones'] ?? ''));
    $respuestas = $input['respuestas'] ?? [];

    if ($id_encuesta <= 0) {
        Api::error(400, 'ID de encuesta no válido o ausente.');
    }
    if (empty($respuestas) || !is_array($respuestas)) {
        Api::error(400, 'El cuerpo de respuestas es obligatorio.');
    }

    $guardado = $modelo->guardarResolucion($id_encuesta, $observaciones, $respuestas);
    if (!$guardado) {
        Api::error($modelo->codigoError, $modelo->ultimoError ?: 'Ocurrió un error al almacenar sus respuestas.');
    }

    Api::responder(201, [
        'status'  => 'ok',
        'mensaje' => '¡Muchas gracias por completar la encuesta! Sus respuestas han sido guardadas de forma anónima.'
    ]);
}

// para editar o borrar encuestas y preguntas se pide rol de gestion o admin
Api::exigirRol(['SUPERADMIN_IT', 'GESTOR_INTEGRAL']);
if ($id === null) {
    Api::error(400, 'Debe indicar el ID en la URL.');
}

if ($metodo === 'PUT') {
    $input = Api::entrada();

    // editar una pregunta
    if ($recurso === 'pregunta' || ($input['recurso'] ?? '') === 'pregunta') {
        $enunciado = (string)($input['enunciado'] ?? '');
        $tipo = (string)($input['tipo'] ?? '');
        $opciones = is_array($input['opciones'] ?? null) ? $input['opciones'] : [];

        if (!$modelo->actualizarPregunta($id, $enunciado, $tipo, $opciones)) {
            Api::error($modelo->codigoError, $modelo->ultimoError ?: 'No se pudo actualizar la pregunta.');
        }
        Api::responder(200, ['status' => 'ok', 'mensaje' => 'Pregunta actualizada correctamente.']);
    }

    // editar datos o estado de la encuesta
    $comentarios = (string)($input['comentarios'] ?? '');
    $estado = (string)($input['estado'] ?? '');
    if (!$modelo->actualizarEncuesta($id, $comentarios, $estado)) {
        Api::error($modelo->codigoError, $modelo->ultimoError ?: 'No se pudo actualizar la encuesta.');
    }
    Api::responder(200, ['status' => 'ok', 'mensaje' => 'Encuesta actualizada correctamente.']);
}

// eliminar una pregunta o una encuesta
if ($recurso === 'pregunta') {
    if (!$modelo->eliminarPregunta($id)) {
        Api::error($modelo->codigoError, $modelo->ultimoError ?: 'No se pudo eliminar la pregunta.');
    }
    Api::responder(200, ['status' => 'ok', 'mensaje' => 'Pregunta eliminada correctamente.']);
}

if (!$modelo->eliminarEncuesta($id)) {
    Api::error($modelo->codigoError, $modelo->ultimoError ?: 'No se pudo eliminar la encuesta.');
}
Api::responder(200, ['status' => 'ok', 'mensaje' => 'Encuesta eliminada correctamente.']);
