<?php

require_once __DIR__ . '/../../backend/config/Api.php';
require_once __DIR__ . '/../../backend/models/Categoria.php';

// api para listar, crear, editar y eliminar categorias
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

// para modificar categorias se necesita rol de gestion o admin
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

// eliminar categoria
if (!$modelo->eliminar($id)) {
    if ($modelo->ultimoError === 'referenciado') {
        Api::error(409, 'No se puede eliminar la categoría porque tiene documentos o encuestas asociadas.');
    }
    Api::error(500, 'Error al eliminar la categoría.');
}
Api::responder(200, ['status' => 'ok', 'mensaje' => 'Categoría eliminada correctamente.']);
