<?php

require_once __DIR__ . '/../../backend/config/Api.php';
require_once __DIR__ . '/../../backend/models/Chofer.php';

// abm de choferes
Api::crudRecurso(new Chofer(), ['SUPERADMIN_IT', 'GESTOR_INTEGRAL', 'DIRECTOR_GENERAL', 'USUARIO_DIVISION'], ['SUPERADMIN_IT', 'GESTOR_INTEGRAL'], 'Chofer');
