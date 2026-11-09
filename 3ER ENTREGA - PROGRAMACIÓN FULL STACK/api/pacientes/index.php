<?php

require_once __DIR__ . '/../../backend/config/Api.php';
require_once __DIR__ . '/../../backend/models/Paciente.php';

// abm de pacientes
$rolesPacientes = ['SUPERADMIN_IT', 'GESTOR_INTEGRAL', 'USUARIO_DIVISION'];
Api::crudRecurso(new Paciente(), $rolesPacientes, $rolesPacientes, 'Paciente');
