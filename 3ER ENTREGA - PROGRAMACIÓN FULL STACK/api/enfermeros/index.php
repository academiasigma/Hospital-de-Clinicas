<?php

require_once __DIR__ . '/../../backend/config/Api.php';
require_once __DIR__ . '/../../backend/models/Enfermero.php';

// REST /api/enfermeros: GET (todos los logueados), POST/PUT/DELETE (transporte y TI).
Api::crudRecurso(new Enfermero(), ['SUPERADMIN_IT', 'GESTOR_INTEGRAL', 'DIRECTOR_GENERAL', 'USUARIO_DIVISION'], ['SUPERADMIN_IT', 'GESTOR_INTEGRAL'], 'Enfermero');
