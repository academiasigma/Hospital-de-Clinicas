<?php

require_once __DIR__ . '/../../backend/config/Api.php';
require_once __DIR__ . '/../../backend/models/Vehiculo.php';

// abm de vehiculos
Api::crudRecurso(new Vehiculo(), ['SUPERADMIN_IT', 'GESTOR_INTEGRAL', 'DIRECTOR_GENERAL', 'USUARIO_DIVISION'], ['SUPERADMIN_IT', 'GESTOR_INTEGRAL'], 'Vehículo');
