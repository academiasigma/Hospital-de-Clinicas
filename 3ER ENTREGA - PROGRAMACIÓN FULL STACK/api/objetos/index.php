<?php

require_once __DIR__ . '/../../backend/config/Api.php';
require_once __DIR__ . '/../../backend/config/Database.php';

// GET /api/objetos: catálogo de objetos trasladables (órganos, muestras, insumos) para el formulario.
Api::iniciar(['GET']);
Api::exigirRol(['SUPERADMIN_IT', 'GESTOR_INTEGRAL', 'DIRECTOR_GENERAL', 'USUARIO_DIVISION']);

$conexion = (new Database())->conectar();
$stmt = $conexion->prepare("SELECT id_objeto, nombre, caracteristicas, tipo_objeto FROM objeto ORDER BY nombre ASC");
$stmt->execute();
$filas = $stmt->fetchAll();
Api::responder(200, ['status' => 'ok', 'total' => count($filas), 'datos' => $filas]);
