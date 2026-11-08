<?php

require_once __DIR__ . '/../../backend/config/Api.php';
require_once __DIR__ . '/../../backend/models/Paciente.php';

// REST /api/pacientes[?documento=]: la división también puede registrar al paciente al pedir un traslado.
$rolesPacientes = ['SUPERADMIN_IT', 'GESTOR_INTEGRAL', 'USUARIO_DIVISION'];
Api::crudRecurso(new Paciente(), $rolesPacientes, $rolesPacientes, 'Paciente');
