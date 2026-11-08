<?php

require_once __DIR__ . '/../../backend/config/Api.php';
require_once __DIR__ . '/../../backend/models/Categoria.php';

/**
 * Endpoint REST de categorías (/api/categorias y /api/categorias/{id}):
 *   GET    -> público (lo consume el portal de invitados y el panel administrativo)
 *   POST   -> crear categoría (SUPERADMIN_IT y GESTOR_INTEGRAL)
 *   PUT    -> actualizar categoría (SUPERADMIN_IT y GESTOR_INTEGRAL)
 *   DELETE -> eliminar categoría sino tiene documentos/encuestas asociadas (FK ON DELETE RESTRICT)
 */
$metodo = Api::iniciar(['GET', 'POST', 'PUT', 'DELETE']);
$id = Api::idDeLaUrl();
$modelo = new Categoria();

if ($metodo === 'GET') {
    if ($id !== null) {
        $fila = $modelo->obtenerPorId($id);
        if ($fila === null) {
            Api::error(404, 'Categoría no encontrada.');
        }
        Api::responder(200, ['status' => 'ok', 'datos' => $fila]);
    }
    $lista = $modelo->listar();
    Api::responder(200, ['status' => 'ok', 'total' => count($lista), 'datos' => $lista]);
}

// Escritura restringida a gestión y administración TI
Api::exigirRol(['SUPERADMIN_IT', 'GESTOR_INTEGRAL']);

if ($metodo === 'POST') {
    $datos = Api::entrada();
    $errores = $modelo->validar($datos);
    if (!empty($errores)) {
        Api::error(400, $errores[0], $errores);
    }
    $nuevoId = $modelo->crear($datos);
    if ($nuevoId === null) {
        if ($modelo->ultimoError === 'duplicado') {
            Api::error(409, 'Ya existe una categoría con ese nombre.');
        }
        Api::error(500, 'Error al registrar la categoría.');
    }
    Api::responder(201, ['status' => 'ok', 'mensaje' => 'Categoría creada correctamente.', 'id' => $nuevoId]);
}

if ($id === null) {
    Api::error(400, 'Debe indicar el ID de la categoría en la URL.');
}
if ($modelo->obtenerPorId($id) === null) {
    Api::error(404, 'Categoría no encontrada.');
}

if ($metodo === 'PUT') {
    $datos = Api::entrada();
    $errores = $modelo->validar($datos);
    if (!empty($errores)) {
        Api::error(400, $errores[0], $errores);
    }
    if (!$modelo->actualizar($id, $datos)) {
        if ($modelo->ultimoError === 'duplicado') {
            Api::error(409, 'Ya existe otra categoría con ese nombre.');
        }
        Api::error(500, 'Error al actualizar la categoría.');
    }
    Api::responder(200, ['status' => 'ok', 'mensaje' => 'Categoría actualizada correctamente.']);
}

// DELETE
if (!$modelo->eliminar($id)) {
    if ($modelo->ultimoError === 'referenciado') {
        Api::error(409, 'No se puede eliminar la categoría porque tiene documentos o encuestas asociadas.');
    }
    Api::error(500, 'Error al eliminar la categoría.');
}
Api::responder(200, ['status' => 'ok', 'mensaje' => 'Categoría eliminada correctamente.']);
