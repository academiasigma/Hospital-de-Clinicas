<?php

require_once __DIR__ . '/../../backend/config/Api.php';
require_once __DIR__ . '/../../backend/models/Encuesta.php';

/**
 * Endpoint REST de encuestas de satisfacción y sus preguntas (/api/encuestas):
 *   GET    /api/encuestas?id_categoria={id}            -> obtiene la encuesta activa (público, portal de pacientes)
 *   GET    /api/encuestas/resultados[?id_categoria=]   -> resumen de resultados y preguntas (requiere sesión)
 *   POST   /api/encuestas                              -> guarda una resolución anónima (público) o crea encuesta/pregunta (gestión)
 *   PUT    /api/encuestas/{id}                         -> actualiza estado/título de encuesta o edita una pregunta (?recurso=pregunta)
 *   DELETE /api/encuestas/{id}?recurso=pregunta        -> elimina una pregunta de la encuesta
 */
$metodo = Api::iniciar(['GET', 'POST', 'PUT', 'DELETE']);
$id = Api::idDeLaUrl();
$recurso = $_GET['recurso'] ?? '';
$modelo = new Encuesta();

if ($metodo === 'GET') {
    // Modo resultados y estructura completa para funcionarios autenticados
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

    // Modo portal de invitados: listar todas las encuestas activas disponibles
    if (!empty($_GET['activas'])) {
        $activas = $modelo->listarActivasPublicas();
        Api::responder(200, ['status' => 'ok', 'encuestas' => $activas]);
    }

    // Modo portal de invitados: obtener una encuesta activa específica por su ID
    if (isset($_GET['id_encuesta']) && is_numeric($_GET['id_encuesta'])) {
        $encuesta = $modelo->obtenerActivaPorId((int)$_GET['id_encuesta']);
        Api::responder(200, ['status' => 'ok', 'encuesta' => $encuesta]);
    }

    // Modo portal de invitados: encuesta activa para una categoría
    $id_categoria = isset($_GET['id_categoria']) ? (int)$_GET['id_categoria'] : 0;
    if ($id_categoria <= 0) {
        Api::error(400, 'ID de categoría no válido.');
    }

    $encuesta = $modelo->obtenerActivaPorCategoria($id_categoria);
    Api::responder(200, ['status' => 'ok', 'encuesta' => $encuesta]);
}

if ($metodo === 'POST') {
    $input = Api::entrada();

    // Si es alta de una nueva pregunta dentro de una encuesta (administración)
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

    // Si es alta de una nueva encuesta para una categoría (administración)
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

    // Caso público por defecto: enviar encuesta anónima terminada desde el Portal de Invitados
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

// PUT y DELETE requieren rol de gestión (SUPERADMIN_IT o GESTOR_INTEGRAL)
Api::exigirRol(['SUPERADMIN_IT', 'GESTOR_INTEGRAL']);
if ($id === null) {
    Api::error(400, 'Debe indicar el ID en la URL.');
}

if ($metodo === 'PUT') {
    $input = Api::entrada();

    // Editar pregunta existente: PUT /api/encuestas/{id_pregunta}?recurso=pregunta
    if ($recurso === 'pregunta' || ($input['recurso'] ?? '') === 'pregunta') {
        $enunciado = (string)($input['enunciado'] ?? '');
        $tipo = (string)($input['tipo'] ?? '');
        $opciones = is_array($input['opciones'] ?? null) ? $input['opciones'] : [];

        if (!$modelo->actualizarPregunta($id, $enunciado, $tipo, $opciones)) {
            Api::error($modelo->codigoError, $modelo->ultimoError ?: 'No se pudo actualizar la pregunta.');
        }
        Api::responder(200, ['status' => 'ok', 'mensaje' => 'Pregunta actualizada correctamente.']);
    }

    // Editar datos/estado de la encuesta: PUT /api/encuestas/{id_encuesta}
    $comentarios = (string)($input['comentarios'] ?? '');
    $estado = (string)($input['estado'] ?? '');
    if (!$modelo->actualizarEncuesta($id, $comentarios, $estado)) {
        Api::error($modelo->codigoError, $modelo->ultimoError ?: 'No se pudo actualizar la encuesta.');
    }
    Api::responder(200, ['status' => 'ok', 'mensaje' => 'Encuesta actualizada correctamente.']);
}

// DELETE /api/encuestas/{id}?recurso=pregunta  o  DELETE /api/encuestas/{id} (encuesta completa)
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
