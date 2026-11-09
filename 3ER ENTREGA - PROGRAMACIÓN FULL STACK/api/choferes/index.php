<?php

require_once __DIR__ . '/../../backend/config/Api.php';
require_once __DIR__ . '/../../backend/models/Chofer.php';

$accion = $_GET['accion'] ?? '';
$modeloChofer = new Chofer();

// Endpoints de sesión y acceso exclusivo para la Terminal Móvil de Chofer
if ($accion === 'sesion') {
    Api::iniciar(['GET']);
    $esSupervisor = Auth::verificarAutenticacion() && Auth::verificarRol(['SUPERADMIN_IT', 'GESTOR_INTEGRAL']);
    $choferSesion = $_SESSION['chofer'] ?? null;
    $listaChoferes = $modeloChofer->listar([]);

    Api::responder(200, [
        'status'        => 'ok',
        'modo'          => $esSupervisor ? 'supervisor' : ($choferSesion ? 'chofer' : 'anonimo'),
        'usuario'       => $esSupervisor ? $_SESSION['usuario'] : null,
        'chofer'        => $choferSesion,
        'choferes_demo' => $listaChoferes
    ]);
}

if ($accion === 'identificar') {
    Api::iniciar(['POST']);
    $datos = Api::entrada();
    $licencia = trim((string)($datos['licencia'] ?? ''));
    if ($licencia === '') {
        Api::error(400, 'Ingrese su número de licencia de conducir.');
    }
    $chofer = $modeloChofer->obtenerPorLicencia($licencia);
    if ($chofer === null) {
        Api::error(401, 'No se encontró ningún chofer registrado con esa licencia.');
    }
    $_SESSION['chofer'] = [
        'id_chofer' => (int)$chofer['id_chofer'],
        'nombre'    => $chofer['nombre'],
        'licencia'  => $chofer['licencia'],
        'telefono'  => $chofer['telefono']
    ];
    Api::responder(200, [
        'status'  => 'ok',
        'mensaje' => 'Identificación de chofer correcta.',
        'chofer'  => $_SESSION['chofer']
    ]);
}

if ($accion === 'salir') {
    Api::iniciar(['POST']);
    unset($_SESSION['chofer']);
    Api::responder(200, ['status' => 'ok', 'mensaje' => 'Sesión de terminal finalizada.']);
}

// REST /api/choferes: GET (todos los logueados), POST/PUT/DELETE (transporte y TI).
Api::crudRecurso($modeloChofer, ['SUPERADMIN_IT', 'GESTOR_INTEGRAL', 'DIRECTOR_GENERAL', 'USUARIO_DIVISION'], ['SUPERADMIN_IT', 'GESTOR_INTEGRAL'], 'Chofer');
