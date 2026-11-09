# Árbol de Directorios del Proyecto

```text
sigsm/
├── sigsm_db.sql
├── api/
│   ├── login.php
│   ├── logout.php
│   ├── sesion.php
│   ├── categorias/
│   │   ├── index.php
│   ├── choferes/
│   │   ├── index.php
│   ├── documentos/
│   │   ├── index.php
│   │   ├── publicos.php
│   ├── encuestas/
│   │   ├── index.php
│   ├── enfermeros/
│   │   ├── index.php
│   ├── objetos/
│   │   ├── index.php
│   ├── pacientes/
│   │   ├── index.php
│   ├── traslados/
│   │   ├── index.php
│   ├── usuarios/
│   │   ├── index.php
│   ├── vehiculos/
│   │   ├── index.php
├── backend/
│   ├── config/
│   │   ├── Api.php
│   │   ├── Auth.php
│   │   ├── Database.php
│   ├── models/
│   │   ├── Categoria.php
│   │   ├── Chofer.php
│   │   ├── Documento.php
│   │   ├── Encuesta.php
│   │   ├── Enfermero.php
│   │   ├── Paciente.php
│   │   ├── Recurso.php
│   │   ├── Traslado.php
│   │   ├── Usuario.php
│   │   ├── Vehiculo.php
├── frontend/
│   ├── index.html
│   ├── css/
│   │   ├── style-ambulancias.css
│   │   ├── style-documentos.css
│   │   ├── style-login.css
│   │   ├── style-panel.css
│   │   ├── style-portal.css
│   ├── html/
│   │   ├── ambulancias-chofer.html
│   │   ├── ambulancias-recursos.html
│   │   ├── ambulancias.html
│   │   ├── documentos.html
│   │   ├── panel.html
│   │   ├── portal-invitados.html
│   │   ├── usuarios.html
│   ├── img/
│   ├── js/
│   │   ├── ambulancias-chofer.js
│   │   ├── ambulancias-recursos.js
│   │   ├── ambulancias.js
│   │   ├── auth.js
│   │   ├── documentos.js
│   │   ├── login.js
│   │   ├── panel.js
│   │   ├── portal-invitados.js
│   │   ├── usuarios.js
├── uploads/
│   ├── documentos/
```

---

# Código Fuente de los Archivos

## Ruta: `sigsm_db.sql`

```sql

SET NAMES utf8mb4;
DROP DATABASE IF EXISTS sigsm_hospital;
CREATE DATABASE sigsm_hospital CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sigsm_hospital;

-- SECCIÓN 1: TABLAS MAESTRAS Y LAS INDEPENDIENTES

CREATE TABLE usuario (
    id_usuario INT AUTO_INCREMENT,
    email VARCHAR(100) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    contrasenia VARCHAR(255) NOT NULL,
    rol ENUM('SUPERADMIN_IT', 'GESTOR_INTEGRAL', 'DIRECTOR_GENERAL', 'USUARIO_DIVISION') NOT NULL,
    estado ENUM('Activo', 'Inactivo') NOT NULL DEFAULT 'Activo',
    CONSTRAINT CP_usuario PRIMARY KEY (id_usuario),
    CONSTRAINT CS_usuario_email UNIQUE (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE chofer (
    id_chofer INT AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    licencia VARCHAR(50) NOT NULL,
    CONSTRAINT CP_chofer PRIMARY KEY (id_chofer),
    CONSTRAINT CS_chofer_licencia UNIQUE (licencia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE enfermero (
    id_enfermero INT AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    matricula_profesional VARCHAR(50) NOT NULL,
    CONSTRAINT CP_enfermero PRIMARY KEY (id_enfermero),
    CONSTRAINT CS_enfermero_matricula UNIQUE (matricula_profesional)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE vehiculo (
    id_vehiculo INT AUTO_INCREMENT,
    tipo_vehiculo ENUM('Ambulancia', 'Auto') NOT NULL,
    matricula VARCHAR(20) NOT NULL,
    estado ENUM('Disponible', 'En Traslado', 'Mantenimiento', 'Fuera de Servicio') NOT NULL,
    CONSTRAINT CP_vehiculo PRIMARY KEY (id_vehiculo),
    CONSTRAINT CS_vehiculo_matricula UNIQUE (matricula)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE paciente (
    id_paciente INT AUTO_INCREMENT,
    documento VARCHAR(20) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    fecha_de_nacimiento DATE NOT NULL,
    requiere_oxigeno TINYINT(1) DEFAULT 0,
    requiere_camilla TINYINT(1) DEFAULT 0,
    requiere_aislamiento TINYINT(1) DEFAULT 0,
    se_maneja_por_sus_medios TINYINT(1) DEFAULT 1,
    CONSTRAINT CP_paciente PRIMARY KEY (id_paciente),
    CONSTRAINT CS_paciente_documento UNIQUE (documento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE objeto (
    id_objeto INT AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    caracteristicas TEXT,
    tipo_objeto ENUM('organo', 'muestra_biologica', 'insumo_medico', 'documento', 'equipamiento') NOT NULL,
    CONSTRAINT CP_objeto PRIMARY KEY (id_objeto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE categoria (
    id_categoria INT AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    CONSTRAINT CP_categoria PRIMARY KEY (id_categoria),
    CONSTRAINT CS_categoria_nombre UNIQUE (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SECCIÓN 2: ENTIDADES TRANSACCIONALES Y DE SOPORTE OPERATIVO

CREATE TABLE documento (
    id_documento INT AUTO_INCREMENT,
    titulo VARCHAR(150) NOT NULL,
    archivo VARCHAR(255) NOT NULL,
    id_usuario INT NOT NULL,
    id_categoria INT NOT NULL,
    CONSTRAINT CP_documento PRIMARY KEY (id_documento),
    CONSTRAINT CE_documento_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT CE_documento_categoria FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE encuesta (
    id_encuesta INT AUTO_INCREMENT,
    comentarios TEXT,
    estado ENUM('Borrador', 'Activa', 'Cerrada') NOT NULL DEFAULT 'Borrador',
    id_categoria INT NOT NULL,
    CONSTRAINT CP_encuesta PRIMARY KEY (id_encuesta),
    CONSTRAINT CE_encuesta_categoria FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE pregunta (
    id_pregunta INT AUTO_INCREMENT,
    enunciado TEXT NOT NULL,
    tipo ENUM('Si/No', 'Texto Libre', 'Escala_1_5', 'Opcion_Multiple') NOT NULL,
    id_encuesta INT NOT NULL,
    CONSTRAINT CP_pregunta PRIMARY KEY (id_pregunta),
    CONSTRAINT CE_pregunta_encuesta FOREIGN KEY (id_encuesta) REFERENCES encuesta(id_encuesta) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE opcion_pregunta (
    id_opcion INT AUTO_INCREMENT,
    texto_opcion VARCHAR(255) NOT NULL,
    id_pregunta INT NOT NULL,
    CONSTRAINT CP_opcion_pregunta PRIMARY KEY (id_opcion),
    CONSTRAINT CE_opcion_pregunta_pregunta FOREIGN KEY (id_pregunta) REFERENCES pregunta(id_pregunta) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE resolucion_encuesta (
    id_resolucion INT AUTO_INCREMENT,
    fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    observaciones TEXT,
    id_encuesta INT NOT NULL,
    CONSTRAINT CP_resolucion_encuesta PRIMARY KEY (id_resolucion),
    CONSTRAINT CE_resolucion_encuesta_encuesta FOREIGN KEY (id_encuesta) REFERENCES encuesta(id_encuesta) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE respuesta (
    id_respuesta INT AUTO_INCREMENT,
    contenido_respuesta TEXT NOT NULL,
    id_resolucion INT NOT NULL,
    id_pregunta INT NOT NULL,
    CONSTRAINT CP_respuesta PRIMARY KEY (id_respuesta),
    CONSTRAINT CE_respuesta_resolucion FOREIGN KEY (id_resolucion) REFERENCES resolucion_encuesta(id_resolucion) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT CE_respuesta_pregunta FOREIGN KEY (id_pregunta) REFERENCES pregunta(id_pregunta) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE traslado (
    id_traslado INT AUTO_INCREMENT,
    fecha DATE NOT NULL,
    hora_salida TIME NOT NULL,
    hora_estimada_llegada TIME NOT NULL,
    hora_efectiva_llegada TIME,
    hora_retorno TIME,
    sector_origen VARCHAR(100) NOT NULL,
    piso_origen VARCHAR(20) NOT NULL,
    habitacion_origen VARCHAR(20),
    sector_destino VARCHAR(100) NOT NULL,
    piso_destino VARCHAR(20) NOT NULL,
    habitacion_destino VARCHAR(20),
    direccion_destino VARCHAR(200) NOT NULL,
    estado ENUM('Pendiente', 'En Curso', 'Retornando', 'Finalizado', 'Cancelado') NOT NULL DEFAULT 'Pendiente',
    prioridad ENUM('Alta', 'Media', 'Baja') NOT NULL DEFAULT 'Media',
    observaciones TEXT,
    division_solicitante VARCHAR(100) NOT NULL,
    motivo_procedimiento VARCHAR(200) NOT NULL,
    responsable_recepcion VARCHAR(100) NOT NULL,
    limite_horario TIME,
    id_vehiculo INT,
    id_chofer INT,
    id_usuario INT NOT NULL,
    id_enfermero INT,
    id_paciente INT,
    id_objeto INT,
    CONSTRAINT CP_traslado PRIMARY KEY (id_traslado),
    CONSTRAINT CE_traslado_vehiculo FOREIGN KEY (id_vehiculo) REFERENCES vehiculo(id_vehiculo) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT CE_traslado_chofer FOREIGN KEY (id_chofer) REFERENCES chofer(id_chofer) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT CE_traslado_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT CE_traslado_enfermero FOREIGN KEY (id_enfermero) REFERENCES enfermero(id_enfermero) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT CE_traslado_paciente FOREIGN KEY (id_paciente) REFERENCES paciente(id_paciente) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT CE_traslado_objeto FOREIGN KEY (id_objeto) REFERENCES objeto(id_objeto) ON DELETE RESTRICT ON UPDATE CASCADE,
    -- RNE03 (paciente u objeto, nunca ambos) se valida en la capa de negocio (Traslado.php): MariaDB no permite CHECK sobre columnas con FK ON UPDATE CASCADE.
    -- RNE01: coherencia cronologica hora_salida < hora_efectiva_llegada <= hora_retorno
    CONSTRAINT CK_traslado_cronologia CHECK (hora_estimada_llegada > hora_salida AND (hora_efectiva_llegada IS NULL OR hora_efectiva_llegada > hora_salida) AND (hora_retorno IS NULL OR hora_efectiva_llegada IS NULL OR hora_retorno >= hora_efectiva_llegada))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- SECCIÓN 3: SCRIPT DML (los datos de Prueba Iniciales)

-- insert de los usuarios iniciales con los roles del sistema
-- Clave de prueba: "password" encriptada con password_hash
INSERT INTO usuario (email, nombre, contrasenia, rol, estado) VALUES 
('superadmin@hc.edu.uy', 'SuperAdmin IT', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'SUPERADMIN_IT', 'Activo'),
('gestor@hc.edu.uy', 'Gestor Integral', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'GESTOR_INTEGRAL', 'Activo'),
('director@hc.edu.uy', 'Director General', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'DIRECTOR_GENERAL', 'Activo'),
('division@hc.edu.uy', 'Usuario Division', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'USUARIO_DIVISION', 'Activo');

-- Insert de categorías médicas
INSERT INTO categoria (nombre) VALUES 
('Indicaciones Médicas y Quirúrgicas'),
('Preparación para Estudios'),
('Enfermería y Trasplantes'),
('Encuestas de Satisfacción'),
('Protocolos de Prevención');

-- Recursos de prueba
INSERT INTO chofer (nombre, telefono, licencia) VALUES 
('Carlos Rodríguez', '099123456', 'LIC-CH-001'),
('Mario Gómez', '098765432', 'LIC-CH-002'),
('Juan Pérez', '097111222', 'LIC-CH-003'),
('Marcelo Silva', '096333444', 'LIC-CH-004'),
('Santiago Pereira', '095555666', 'LIC-CH-005'),
('Jorge González', '094777888', 'LIC-CH-006'),
('Alejandro Rodríguez', '093999000', 'LIC-CH-007'),
('Néstor Cabrera', '092111333', 'LIC-CH-008');

INSERT INTO enfermero (nombre, telefono, matricula_profesional) VALUES 
('Ana Martínez', '091112233', 'ENF-MAT-101');

INSERT INTO vehiculo (tipo_vehiculo, matricula, estado) VALUES 
('Ambulancia', 'SCA-1001', 'Disponible'),
('Auto', 'SCA-2002', 'Disponible');

-- Pacientes de prueba (algunas cédulas para pruebas)
INSERT INTO paciente (documento, nombre, telefono, fecha_de_nacimiento, requiere_oxigeno, requiere_camilla, requiere_aislamiento, se_maneja_por_sus_medios) VALUES 
('57327104', 'Facundo Guedikian', '099111222', '1995-01-01', 0, 0, 0, 1),
('57915575', 'Ihojan Robaina', '099222333', '1996-02-02', 0, 0, 0, 1),
('57516640', 'Ezequiel Gomez', '099333444', '1997-03-03', 0, 0, 0, 1),
('57700578', 'Nicolas Chaitelle', '099444555', '1998-04-04', 0, 0, 0, 1);

-- Objetos de prueba (Módulo de Traslados y ambulancias)
INSERT INTO objeto (nombre, caracteristicas, tipo_objeto) VALUES 
('Órgano: Corazón para Trasplante', 'Caja térmica refrigerada, transporte urgente, prioridad alta', 'organo'),
('Muestra Biológica: Biopsia urgente', 'Tubo refrigerado para laboratorio de patología', 'muestra_biologica'),
('Insumos: Medicamentos Oncológicos', 'Caja sellada para farmacia, mantener en temperatura fresca', 'insumo_medico');

-- Documentos clínicos iniciales
INSERT INTO documento (titulo, archivo, id_usuario, id_categoria) VALUES 
('Indicaciones de interrupción voluntaria del embarazo', 'uploads/documentos/ive_indicaciones.pdf', 1, 1),
('Preparación para ecocardiograma transesofágico', 'uploads/documentos/ecocardiograma_prep.pdf', 1, 2),
('Plan de alta de enfermería en Nefrología', 'uploads/documentos/nefrologia_alta.pdf', 1, 3);

-- Encuestas activas de prueba (Módulo de Satisfacción del Paciente)
INSERT INTO encuesta (id_encuesta, comentarios, estado, id_categoria) VALUES 
(1, 'Encuesta del Área Ginecología/IVE', 'Activa', 1),
(2, 'Encuesta del Área Estudios Imagenológicos', 'Activa', 2),
(3, 'Encuesta del Área Enfermería y Trasplantes', 'Activa', 3);

-- PREGUNTAS ASOCIADAS A LA ENCUESTA 1 (Categoría 1)
INSERT INTO pregunta (id_pregunta, enunciado, tipo, id_encuesta) VALUES 
(1, '¿La información provista en la guía clínica fue clara y fácil de entender?', 'Si/No', 1),
(2, 'Califique la calidad de la atención médica recibida durante su consulta:', 'Escala_1_5', 1),
(3, 'Sugerencias o comentarios adicionales para mejorar este instructivo:', 'Texto Libre', 1);

-- PREGUNTAS ASOCIADAS A LA ENCUESTA 2 (Categoría 2)
INSERT INTO pregunta (id_pregunta, enunciado, tipo, id_encuesta) VALUES 
(4, '¿Le resultó sencillo seguir las indicaciones de ayuno y preparación para su estudio?', 'Si/No', 2),
(5, 'Califique la amabilidad y el respeto mostrado por el personal técnico:', 'Escala_1_5', 2),
(6, 'Deje sus comentarios sobre su experiencia general de preparación:', 'Texto Libre', 2);

-- PREGUNTAS ASOCIADAS A LA ENCUESTA 3 ( Categoría 3)
INSERT INTO pregunta (id_pregunta, enunciado, tipo, id_encuesta) VALUES 
(7, '¿La explicación de los cuidados post-alta domiciliaria por parte del enfermero fue clara?', 'Si/No', 3),
(8, 'Califique la preparación técnica y el trato brindado por el personal de enfermería:', 'Escala_1_5', 3),
(9, 'Escriba aquí sus observaciones o sugerencias para mejorar el plan de alta:', 'Texto Libre', 3);

-- SECCION 4: USUARIOS DE BASE DE DATOS CON DISTINTOS PRIVILEGIOS
-- Se recrea sigsm_app para garantizar que tanto en Docker como en XAMPP/CentOS tenga unicamente permisos DML
DROP USER IF EXISTS 'sigsm_app'@'%';
DROP USER IF EXISTS 'sigsm_app'@'localhost';
CREATE USER 'sigsm_app'@'%' IDENTIFIED BY 'Hc1234_db';
CREATE USER 'sigsm_app'@'localhost' IDENTIFIED BY 'Hc1234_db';
GRANT SELECT, INSERT, UPDATE, DELETE ON sigsm_hospital.* TO 'sigsm_app'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE ON sigsm_hospital.* TO 'sigsm_app'@'localhost';

-- Usuario administrador: control total sobre la base de datos
CREATE USER IF NOT EXISTS 'sigsm_admin'@'%' IDENTIFIED BY 'Hc1234_admin';
CREATE USER IF NOT EXISTS 'sigsm_admin'@'localhost' IDENTIFIED BY 'Hc1234_admin';
GRANT ALL PRIVILEGES ON sigsm_hospital.* TO 'sigsm_admin'@'%';
GRANT ALL PRIVILEGES ON sigsm_hospital.* TO 'sigsm_admin'@'localhost';

FLUSH PRIVILEGES;

```

---

## Ruta: `api\login.php`

```php
<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../backend/config/Auth.php';
require_once __DIR__ . '/../backend/models/Usuario.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'mensaje' => 'Método no permitido. Utilice POST.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$email = trim($input['email'] ?? $_POST['email'] ?? '');
$password = trim($input['password'] ?? $_POST['password'] ?? '');

if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'mensaje' => 'El correo electrónico y la contraseña son obligatorios.']);
    exit;
}

$modeloUsuario = new Usuario();
$resultado = $modeloUsuario->autenticar($email, $password);

if (!$resultado['exito']) {
    http_response_code($resultado['codigo']);
    echo json_encode([
        'status'  => 'error',
        'mensaje' => $resultado['mensaje']
    ]);
    exit;
}

Auth::iniciarSesion();
$_SESSION['usuario'] = $resultado['usuario'];

http_response_code(200);
echo json_encode([
    'status'  => 'ok',
    'mensaje' => $resultado['mensaje'],
    'usuario' => $resultado['usuario']
]);

```

---

## Ruta: `api\logout.php`

```php
<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__ . '/../backend/config/Auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'mensaje' => 'Método no permitido.']);
    exit;
}

Auth::iniciarSesion();
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

http_response_code(200);
echo json_encode([
    'status'  => 'ok',
    'mensaje' => 'Sesión cerrada correctamente.'
]);

```

---

## Ruta: `api\sesion.php`

```php
<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once __DIR__ . '/../backend/config/Auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'mensaje' => 'Método no permitido.']);
    exit;
}

if (!Auth::verificarAutenticacion()) {
    http_response_code(401);
    echo json_encode([
        'status'        => 'error',
        'autenticado'   => false,
        'mensaje'       => 'No hay sesión activa.'
    ]);
    exit;
}

http_response_code(200);
echo json_encode([
    'status'      => 'ok',
    'autenticado' => true,
    'usuario'     => $_SESSION['usuario']
]);

```

---

## Ruta: `api\categorias\index.php`

```php
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

```

---

## Ruta: `api\choferes\index.php`

```php
<?php

require_once __DIR__ . '/../../backend/config/Api.php';
require_once __DIR__ . '/../../backend/models/Chofer.php';

// REST /api/choferes: GET (todos los logueados), POST/PUT/DELETE (transporte y TI).
Api::crudRecurso(new Chofer(), ['SUPERADMIN_IT', 'GESTOR_INTEGRAL', 'DIRECTOR_GENERAL', 'USUARIO_DIVISION'], ['SUPERADMIN_IT', 'GESTOR_INTEGRAL'], 'Chofer');

```

---

## Ruta: `api\documentos\index.php`

```php
<?php

require_once __DIR__ . '/../../backend/config/Api.php';
require_once __DIR__ . '/../../backend/models/Documento.php';

/**
 * Endpoint REST de documentos clínicos (/api/documentos y /api/documentos/{id}):
 *   GET    /api/documentos[?id_categoria=] -> listado
 *   GET    /api/documentos/{id}            -> detalle de un documento
 *   POST   /api/documentos                 -> alta (multipart/form-data con archivo, o reemplazo de archivo si viene _method=PUT)
 *   PUT    /api/documentos/{id}            -> edición de metadatos (application/json: titulo, id_categoria)
 *   DELETE /api/documentos/{id}            -> eliminación física y lógica
 *
 * Siguiendo el estándar de Ejemplo2 de clase, se valida el tamaño máximo (10 MB),
 * el tipo MIME real con finfo_file() y la firma del archivo (%PDF- en caso de PDF).
 */
header('X-Content-Type-Options: nosniff');

$metodo = Api::iniciar(['GET', 'POST', 'PUT', 'DELETE']);
$id = Api::idDeLaUrl();
$modelo = new Documento();
$tamanoMaximo = 10 * 1024 * 1024; // 10 MB

// Auxiliar para validar y guardar un archivo subido en $_FILES['archivo']
$procesarArchivoSubido = function (array $archivo) use ($tamanoMaximo): string {
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        Api::error(400, 'Debe adjuntar un archivo válido (PDF o Imagen).');
    }
    if (!is_uploaded_file($archivo['tmp_name']) || $archivo['size'] <= 0) {
        Api::error(400, 'El archivo recibido no es válido.');
    }
    if ($archivo['size'] > $tamanoMaximo) {
        Api::error(400, 'El archivo supera el límite permitido de 10 MB.');
    }

    $nombreOriginal = basename(str_replace('\\', '/', $archivo['name']));
    $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
    $extensionesPermitidas = ['pdf', 'jpg', 'jpeg', 'png'];
    if (!in_array($extension, $extensionesPermitidas, true)) {
        Api::error(400, 'Formato no permitido. Solo se aceptan PDF, JPG o PNG.');
    }

    // Validación de tipo MIME real e inspección de firma binaria (como en Ejemplo2 de clase)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $tipoMime = finfo_file($finfo, $archivo['tmp_name']);
    finfo_close($finfo);

    $mimesPermitidos = ['application/pdf', 'image/jpeg', 'image/png'];
    if (!in_array($tipoMime, $mimesPermitidos, true)) {
        Api::error(400, 'El contenido real del archivo no corresponde a un PDF o imagen válida.');
    }
    if ($extension === 'pdf') {
        $handle = fopen($archivo['tmp_name'], 'rb');
        $cabecera = fread($handle, 5);
        fclose($handle);
        if ($cabecera !== '%PDF-') {
            Api::error(400, 'El archivo debe ser un PDF válido.');
        }
    }

    $carpetaDestino = __DIR__ . '/../../uploads/documentos/';
    if (!is_dir($carpetaDestino) && !mkdir($carpetaDestino, 0775, true) && !is_dir($carpetaDestino)) {
        Api::error(500, 'No se pudo crear la carpeta de destino en el servidor.');
    }

    $nombreArchivoGuardado = 'doc_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $rutaFinalServidor = $carpetaDestino . $nombreArchivoGuardado;

    if (!move_uploaded_file($archivo['tmp_name'], $rutaFinalServidor)) {
        Api::error(500, 'Fallo al guardar el archivo en el servidor.');
    }

    return 'uploads/documentos/' . $nombreArchivoGuardado;
};

// GET: listado general o detalle por ID
if ($metodo === 'GET') {
    if ($id !== null) {
        $doc = $modelo->obtenerPorId($id);
        if ($doc === null) {
            Api::error(404, 'Documento no encontrado.');
        }
        Api::responder(200, ['status' => 'ok', 'datos' => $doc]);
    }

    $idCategoria = isset($_GET['id_categoria']) && is_numeric($_GET['id_categoria'])
        ? (int)$_GET['id_categoria']
        : null;

    $documentos = $modelo->listarTodos($idCategoria);
    Api::responder(200, [
        'status' => 'ok',
        'total'  => count($documentos),
        'datos'  => $documentos
    ]);
}

// Operaciones de escritura requieren sesión activa y rol con permisos de gestión documental
Api::exigirRol(['SUPERADMIN_IT', 'GESTOR_INTEGRAL', 'USUARIO_DIVISION']);

// Si es POST pero incluye id en la URL o _method=PUT (usado cuando se reemplaza el archivo adjunto por multipart/form-data)
if ($metodo === 'POST' && ($id !== null || strtoupper($_POST['_method'] ?? '') === 'PUT' || !empty($_POST['id_documento']))) {
    $idEditar = $id ?? (int)($_POST['id_documento'] ?? 0);
    if ($idEditar <= 0 || $modelo->obtenerPorId($idEditar) === null) {
        Api::error(404, 'Documento no encontrado.');
    }

    $titulo = trim($_POST['titulo'] ?? '');
    $id_categoria = isset($_POST['id_categoria']) ? (int)$_POST['id_categoria'] : 0;
    if ($titulo === '' || mb_strlen($titulo) > 150 || $id_categoria <= 0) {
        Api::error(400, 'El título (máx. 150 caracteres) y la categoría son obligatorios.');
    }

    $rutaBD = null;
    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $rutaBD = $procesarArchivoSubido($_FILES['archivo']);
    }

    if (!$modelo->actualizar($idEditar, [
        'titulo'       => $titulo,
        'id_categoria' => $id_categoria,
        'archivo'      => $rutaBD
    ])) {
        Api::error(500, 'No se pudo actualizar el registro en la base de datos.');
    }

    Api::responder(200, ['status' => 'ok', 'mensaje' => 'Documento modificado correctamente.']);
}

// POST: Alta de nuevo documento
if ($metodo === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $id_categoria = isset($_POST['id_categoria']) ? (int)$_POST['id_categoria'] : 0;
    $id_usuario = (int)$_SESSION['usuario']['id_usuario'];

    if ($titulo === '' || mb_strlen($titulo) > 150 || $id_categoria <= 0) {
        Api::error(400, 'El título (máx. 150 caracteres) y la categoría son obligatorios.');
    }
    if (!isset($_FILES['archivo'])) {
        Api::error(400, 'Debe adjuntar un archivo válido (PDF o Imagen).');
    }

    $rutaBD = $procesarArchivoSubido($_FILES['archivo']);

    try {
        $exito = $modelo->crear([
            'titulo'       => $titulo,
            'archivo'      => $rutaBD,
            'id_usuario'   => $id_usuario,
            'id_categoria' => $id_categoria
        ]);
    } catch (Throwable $e) {
        $exito = false;
    }

    if (!$exito) {
        $rutaFisica = __DIR__ . '/../../' . $rutaBD;
        if (is_file($rutaFisica)) {
            unlink($rutaFisica);
        }
        Api::error(500, 'Error al registrar el documento en la base de datos.');
    }

    Api::responder(201, ['status' => 'ok', 'mensaje' => 'Documento registrado exitosamente.']);
}

// PUT: Actualizar metadatos del documento (/api/documentos/{id}) con cuerpo JSON
if ($metodo === 'PUT') {
    $datos = Api::entrada();
    $idEditar = $id ?? (int)($datos['id_documento'] ?? 0);
    if ($idEditar <= 0) {
        Api::error(400, 'Debe indicar el ID del documento a actualizar.');
    }
    if ($modelo->obtenerPorId($idEditar) === null) {
        Api::error(404, 'Documento no encontrado.');
    }

    $titulo = trim((string)($datos['titulo'] ?? ''));
    $id_categoria = (int)($datos['id_categoria'] ?? 0);

    if ($titulo === '' || mb_strlen($titulo) > 150 || $id_categoria <= 0) {
        Api::error(400, 'El título (máx. 150 caracteres) y la categoría son obligatorios.');
    }

    $exito = $modelo->actualizar($idEditar, [
        'titulo'       => $titulo,
        'id_categoria' => $id_categoria,
        'archivo'      => null
    ]);

    if (!$exito) {
        Api::error(500, 'No se pudo actualizar el documento.');
    }

    Api::responder(200, ['status' => 'ok', 'mensaje' => 'Documento modificado correctamente.']);
}

// DELETE: Eliminar documento (/api/documentos/{id})
if ($metodo === 'DELETE') {
    $idEliminar = $id;
    if ($idEliminar === null) {
        $datos = Api::entrada();
        $idEliminar = isset($datos['id_documento']) ? (int)$datos['id_documento'] : null;
    }
    if ($idEliminar === null || $idEliminar <= 0) {
        Api::error(400, 'Debe indicar el ID del documento a eliminar.');
    }
    if ($modelo->obtenerPorId($idEliminar) === null) {
        Api::error(404, 'Documento no encontrado.');
    }

    if (!$modelo->eliminar($idEliminar)) {
        Api::error(500, 'No se pudo eliminar el documento.');
    }

    Api::responder(200, ['status' => 'ok', 'mensaje' => 'Documento eliminado exitosamente.']);
}

```

---

## Ruta: `api\documentos\publicos.php`

```php
<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once __DIR__ . '/../../backend/config/Database.php';
require_once __DIR__ . '/../../backend/models/Documento.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'mensaje' => 'Método no permitido.']);
    exit;
}

$modelo = new Documento();
$documentos = $modelo->listarPublicos();


$datosFormateados = [];
foreach ($documentos as $doc) {
    

    $datosFormateados[] = [
        'id_documento'     => (int)$doc['id_documento'],
        'titulo'           => $doc['titulo'],
        'categoria_nombre' => $doc['categoria_nombre'],
        'id_categoria'     => (int)$doc['id_categoria'],
        'archivo'          => $doc['archivo'] //Acceso directo al doc
    ];
}

http_response_code(200);
echo json_encode([
    'status'          => 'ok',
    'documentos'      => $datosFormateados

]);

```

---

## Ruta: `api\encuestas\index.php`

```php
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

```

---

## Ruta: `api\enfermeros\index.php`

```php
<?php

require_once __DIR__ . '/../../backend/config/Api.php';
require_once __DIR__ . '/../../backend/models/Enfermero.php';

// REST /api/enfermeros: GET (todos los logueados), POST/PUT/DELETE (transporte y TI).
Api::crudRecurso(new Enfermero(), ['SUPERADMIN_IT', 'GESTOR_INTEGRAL', 'DIRECTOR_GENERAL', 'USUARIO_DIVISION'], ['SUPERADMIN_IT', 'GESTOR_INTEGRAL'], 'Enfermero');

```

---

## Ruta: `api\objetos\index.php`

```php
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

```

---

## Ruta: `api\pacientes\index.php`

```php
<?php

require_once __DIR__ . '/../../backend/config/Api.php';
require_once __DIR__ . '/../../backend/models/Paciente.php';

// REST /api/pacientes[?documento=]: la división también puede registrar al paciente al pedir un traslado.
$rolesPacientes = ['SUPERADMIN_IT', 'GESTOR_INTEGRAL', 'USUARIO_DIVISION'];
Api::crudRecurso(new Paciente(), $rolesPacientes, $rolesPacientes, 'Paciente');

```

---

## Ruta: `api\traslados\index.php`

```php
<?php

require_once __DIR__ . '/../../backend/config/Api.php';
require_once __DIR__ . '/../../backend/models/Traslado.php';
require_once __DIR__ . '/../../backend/models/Paciente.php';

/**
 * Endpoint REST de traslados:
 *   GET    /api/traslados[?id_paciente=&documento=&id_vehiculo=&matricula=&estado=&fecha_desde=&fecha_hasta=]
 *   GET    /api/traslados/{id}
 *   POST   /api/traslados                 (alta; la división solo crea la solicitud)
 *   PUT    /api/traslados/{id}            (edición / asignación de recursos, solo si está Pendiente)
 *   PUT    /api/traslados/{id}/estado     (seguimiento: {estado, hora_efectiva_llegada?, hora_retorno?})
 *   DELETE /api/traslados/{id}            (cancelación lógica)
 */
const ROLES_TRANSPORTE = ['SUPERADMIN_IT', 'GESTOR_INTEGRAL'];

$metodo = Api::iniciar(['GET', 'POST', 'PUT', 'DELETE']);
$id = Api::idDeLaUrl();
$accion = $_GET['accion'] ?? '';
$traslado = new Traslado();

// Responde con los errores del modelo y el código HTTP que corresponde.
$responderErrorModelo = function () use ($traslado) {
    Api::error($traslado->codigoError, $traslado->errores[0] ?? 'Error al procesar el traslado.', $traslado->errores);
};

if ($metodo === 'GET') {
    Api::exigirRol(['SUPERADMIN_IT', 'GESTOR_INTEGRAL', 'DIRECTOR_GENERAL', 'USUARIO_DIVISION']);
    $rol = $_SESSION['usuario']['rol'];
    // Un usuario de división solo ve las solicitudes que él creó.
    $soloPropios = ($rol === 'USUARIO_DIVISION') ? (int)$_SESSION['usuario']['id_usuario'] : null;

    if ($id !== null) {
        $fila = $traslado->obtenerPorId($id);
        if ($fila === null || ($soloPropios !== null && (int)$fila['id_usuario'] !== $soloPropios)) {
            Api::error(404, 'Traslado no encontrado.');
        }
        Api::responder(200, ['status' => 'ok', 'datos' => $fila]);
    }

    $filas = $traslado->listar($_GET, $soloPropios);
    Api::responder(200, ['status' => 'ok', 'total' => count($filas), 'datos' => $filas]);
}

if ($metodo === 'POST') {
    Api::exigirRol(['SUPERADMIN_IT', 'GESTOR_INTEGRAL', 'USUARIO_DIVISION']);
    $datos = Api::entrada();
    $esDivision = ($_SESSION['usuario']['rol'] === 'USUARIO_DIVISION');

    // Ingreso manual del paciente: si viene el objeto "paciente" se crea o reutiliza por documento.
    if (isset($datos['paciente']) && is_array($datos['paciente'])) {
        $modeloPaciente = new Paciente();
        $erroresPaciente = $modeloPaciente->validar($datos['paciente']);
        if (!empty($erroresPaciente)) {
            Api::error(400, 'Datos del paciente inválidos.', $erroresPaciente);
        }
        $idPaciente = $modeloPaciente->crear($datos['paciente']);
        if ($idPaciente === null) {
            Api::error(500, 'No se pudo registrar al paciente.');
        }
        $datos['id_paciente'] = $idPaciente;
    }

    $nuevoId = $traslado->crear($datos, (int)$_SESSION['usuario']['id_usuario'], $esDivision);
    if ($nuevoId === null) {
        $responderErrorModelo();
    }
    Api::responder(201, ['status' => 'ok', 'mensaje' => 'Traslado registrado.', 'id' => $nuevoId]);
}

// PUT y DELETE: solo el área de transporte y el administrador TI.
Api::exigirRol(ROLES_TRANSPORTE);
if ($id === null) {
    Api::error(400, 'Debe indicar el id del traslado en la URL.');
}

if ($metodo === 'PUT' && $accion === 'estado') {
    $datos = Api::entrada();
    $nuevoEstado = trim((string)($datos['estado'] ?? ''));
    if ($nuevoEstado === '') {
        Api::error(400, 'El campo estado es obligatorio.');
    }
    $ok = $traslado->cambiarEstado($id, $nuevoEstado, $datos['hora_efectiva_llegada'] ?? null, $datos['hora_retorno'] ?? null);
    if (!$ok) {
        $responderErrorModelo();
    }
    Api::responder(200, ['status' => 'ok', 'mensaje' => "Estado actualizado a $nuevoEstado."]);
}

if ($metodo === 'PUT') {
    if (!$traslado->actualizar($id, Api::entrada())) {
        $responderErrorModelo();
    }
    Api::responder(200, ['status' => 'ok', 'mensaje' => 'Traslado actualizado.']);
}

// DELETE: cancelación lógica, el traslado se conserva en el historial.
if (!$traslado->cancelar($id)) {
    $responderErrorModelo();
}
Api::responder(200, ['status' => 'ok', 'mensaje' => 'Traslado cancelado.']);

```

---

## Ruta: `api\usuarios\index.php`

```php
<?php

require_once __DIR__ . '/../../backend/config/Api.php';
require_once __DIR__ . '/../../backend/models/Usuario.php';

/**
 * Endpoint REST de usuarios (/api/usuarios y /api/usuarios/{id}):
 *   GET    /api/usuarios       -> listar funcionarios (requiere sesión)
 *   GET    /api/usuarios/{id}  -> detalle de un funcionario
 *   POST   /api/usuarios       -> alta de funcionario (solo SUPERADMIN_IT, RNE11/RNE12)
 *   PUT    /api/usuarios/{id}  -> edición de funcionario (solo SUPERADMIN_IT, RNE11/RNE12)
 *   DELETE /api/usuarios/{id}  -> baja de funcionario (solo SUPERADMIN_IT, RNE11)
 */
$metodo = Api::iniciar(['GET', 'POST', 'PUT', 'DELETE']);
$id = Api::idDeLaUrl();
$modelo = new Usuario();

$rolesValidos = ['SUPERADMIN_IT', 'GESTOR_INTEGRAL', 'DIRECTOR_GENERAL', 'USUARIO_DIVISION'];
$estadosValidos = ['Activo', 'Inactivo'];

if ($metodo === 'GET') {
    Api::exigirRol($rolesValidos);

    if ($id !== null) {
        $usr = $modelo->obtenerPorId($id);
        if ($usr === null) {
            Api::error(404, 'Usuario no encontrado.');
        }
        unset($usr['contrasenia']);
        Api::responder(200, ['status' => 'ok', 'datos' => $usr]);
    }

    $usuarios = $modelo->listarTodos();
    $datosFormateados = [];
    foreach ($usuarios as $usr) {
        $datosFormateados[] = [
            'id_usuario' => (int)$usr['id_usuario'],
            'email'      => $usr['email'],
            'nombre'     => $usr['nombre'],
            'rol'        => $usr['rol'],
            'estado'     => $usr['estado']
        ];
    }

    Api::responder(200, ['status' => 'ok', 'usuarios' => $datosFormateados]);
}

// RNE11: Solo SUPERADMIN_IT puede crear, editar o eliminar cuentas de usuarios
Api::exigirRol(['SUPERADMIN_IT']);

if ($metodo === 'POST') {
    $input = Api::entrada();

    $email = trim($input['email'] ?? '');
    $nombre = trim($input['nombre'] ?? '');
    $contrasenia = trim($input['contrasenia'] ?? '');
    $rol = trim($input['rol'] ?? '');
    $estado = trim($input['estado'] ?? 'Activo');

    if ($email === '' || $nombre === '' || $contrasenia === '' || $rol === '') {
        Api::error(400, 'Todos los campos son obligatorios (Nombre, Email, Contraseña, Rol).');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        Api::error(400, 'El formato del correo electrónico es inválido.');
    }
    if (!in_array($rol, $rolesValidos, true) || !in_array($estado, $estadosValidos, true)) {
        Api::error(400, 'El rol o el estado indicado no es válido.');
    }
    // RNE12: Unicidad de email
    if ($modelo->obtenerPorEmail($email) !== null) {
        Api::error(409, 'El correo electrónico ya se encuentra registrado.');
    }

    $creado = $modelo->crear([
        'email'       => $email,
        'nombre'      => $nombre,
        'contrasenia' => $contrasenia,
        'rol'         => $rol,
        'estado'      => $estado
    ]);

    if (!$creado) {
        Api::error(500, 'Error en el servidor al registrar el usuario.');
    }

    Api::responder(201, ['status' => 'ok', 'mensaje' => 'Funcionario registrado exitosamente.']);
}

if ($metodo === 'PUT') {
    $input = Api::entrada();
    $id_usuario = $id ?? (int)($input['id_usuario'] ?? 0);
    $email = trim($input['email'] ?? '');
    $nombre = trim($input['nombre'] ?? '');
    $contrasenia = trim($input['contrasenia'] ?? '');
    $rol = trim($input['rol'] ?? '');
    $estado = trim($input['estado'] ?? '');

    if ($id_usuario <= 0 || $email === '' || $nombre === '' || $rol === '' || $estado === '') {
        Api::error(400, 'Faltan datos obligatorios para actualizar el usuario.');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        Api::error(400, 'El formato del correo electrónico es inválido.');
    }
    if (!in_array($rol, $rolesValidos, true) || !in_array($estado, $estadosValidos, true)) {
        Api::error(400, 'El rol o el estado indicado no es válido.');
    }
    if ($modelo->obtenerPorId($id_usuario) === null) {
        Api::error(404, 'Usuario no encontrado.');
    }

    $usuarioExistente = $modelo->obtenerPorEmail($email);
    if ($usuarioExistente !== null && (int)$usuarioExistente['id_usuario'] !== $id_usuario) {
        Api::error(409, 'El correo electrónico ya se encuentra registrado por otro funcionario.');
    }

    if ((int)$_SESSION['usuario']['id_usuario'] === $id_usuario) {
        if ($rol !== 'SUPERADMIN_IT' || $estado !== 'Activo') {
            Api::error(400, 'No puedes cambiar tu propio nivel de acceso ni suspender tu cuenta activa.');
        }
    }

    $actualizado = $modelo->actualizar($id_usuario, [
        'email'       => $email,
        'nombre'      => $nombre,
        'rol'         => $rol,
        'estado'      => $estado,
        'contrasenia' => $contrasenia !== '' ? $contrasenia : null
    ]);

    if (!$actualizado) {
        Api::error(500, 'Ocurrió un error en el servidor al intentar actualizar el funcionario.');
    }

    Api::responder(200, ['status' => 'ok', 'mensaje' => 'Funcionario actualizado con éxito.']);
}

// DELETE
$id_usuario = $id;
if ($id_usuario === null) {
    $input = Api::entrada();
    $id_usuario = isset($input['id_usuario']) ? (int)$input['id_usuario'] : null;
}
if ($id_usuario === null || $id_usuario <= 0) {
    Api::error(400, 'ID de usuario no válido.');
}
if ($id_usuario === (int)$_SESSION['usuario']['id_usuario']) {
    Api::error(400, 'No puede eliminar su propia cuenta activa.');
}
if ($modelo->obtenerPorId($id_usuario) === null) {
    Api::error(404, 'Usuario no encontrado.');
}

try {
    $eliminado = $modelo->eliminar($id_usuario);
} catch (PDOException $e) {
    if (($e->errorInfo[1] ?? 0) === 1451) {
        Api::error(409, 'No se puede eliminar el funcionario porque tiene documentos o traslados registrados. Puede cambiar su estado a Inactivo.');
    }
    Api::error(500, 'Error al eliminar el usuario del sistema.');
}

if (!$eliminado) {
    Api::error(500, 'Error al eliminar el usuario del sistema.');
}

Api::responder(200, ['status' => 'ok', 'mensaje' => 'Usuario eliminado de forma definitiva.']);

```

---

## Ruta: `api\vehiculos\index.php`

```php
<?php

require_once __DIR__ . '/../../backend/config/Api.php';
require_once __DIR__ . '/../../backend/models/Vehiculo.php';

// REST /api/vehiculos: GET (todos los logueados), POST/PUT/DELETE (transporte y TI).
Api::crudRecurso(new Vehiculo(), ['SUPERADMIN_IT', 'GESTOR_INTEGRAL', 'DIRECTOR_GENERAL', 'USUARIO_DIVISION'], ['SUPERADMIN_IT', 'GESTOR_INTEGRAL'], 'Vehículo');

```

---

## Ruta: `backend\config\Api.php`

```php
<?php

require_once __DIR__ . '/Auth.php';

date_default_timezone_set('America/Montevideo');

/**
 * Utilidades comunes de la capa de presentación (endpoints REST).
 * Centraliza: validación del método HTTP, control de sesión/rol, lectura del cuerpo JSON
 * y el formato uniforme de las respuestas.
 */
class Api {

    /**
     * Prepara la respuesta JSON, valida que el método HTTP sea uno de los permitidos
     * (si no, responde 405) y arranca la sesión. Devuelve el método de la petición.
     */
    public static function iniciar(array $metodosPermitidos): string {
        header('Content-Type: application/json; charset=utf-8');
        header('Allow: ' . implode(', ', $metodosPermitidos));
        Auth::iniciarSesion();

        $metodo = $_SERVER['REQUEST_METHOD'];
        if (!in_array($metodo, $metodosPermitidos, true)) {
            self::error(405, 'Método no permitido.');
        }
        return $metodo;
    }

    // Envía una respuesta JSON y termina la ejecución.
    public static function responder(int $codigo, array $datos): void {
        http_response_code($codigo);
        echo json_encode($datos, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Respuesta de error con formato uniforme. $errores es opcional (lista de mensajes de validación).
    public static function error(int $codigo, string $mensaje, array $errores = []): void {
        $respuesta = ['status' => 'error', 'mensaje' => $mensaje];
        if (!empty($errores)) {
            $respuesta['errores'] = $errores;
        }
        self::responder($codigo, $respuesta);
    }

    // Lee el cuerpo JSON de la petición (POST/PUT). Si no es JSON válido responde 400.
    public static function entrada(): array {
        $crudo = file_get_contents('php://input');
        if ($crudo === '' || $crudo === false) {
            return [];
        }
        $datos = json_decode($crudo, true);
        if (!is_array($datos)) {
            self::error(400, 'El cuerpo de la petición debe ser un JSON válido.');
        }
        return $datos;
    }

    // Exige sesión activa (401) y que el rol esté entre los permitidos (403).
    public static function exigirRol(array $rolesPermitidos): void {
        if (!Auth::verificarAutenticacion()) {
            self::error(401, 'No autorizado. Debe iniciar sesión.');
        }
        if (!Auth::verificarRol($rolesPermitidos)) {
            self::error(403, 'Privilegios insuficientes para esta operación.');
        }
    }

    // Devuelve el id numérico de la URL (/api/recurso/{id} o ?id={id}) o null si no vino.
    public static function idDeLaUrl(): ?int {
        $id = $_GET['id'] ?? '';
        if (is_string($id) && ctype_digit($id)) {
            return (int)$id;
        }
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
        if (preg_match('#/([0-9]+)(?:/(?:estado|resultados))?/?$#', $uri, $m)) {
            return (int)$m[1];
        }
        return null;
    }

    /**
     * Controlador REST genérico para los recursos simples (choferes, enfermeros, vehículos, pacientes).
     * GET lista/obtiene, POST crea, PUT edita y DELETE elimina. La escritura se limita a $rolesEscritura.
     */
    public static function crudRecurso(Recurso $modelo, array $rolesLectura, array $rolesEscritura, string $etiqueta): void {
        $metodo = self::iniciar(['GET', 'POST', 'PUT', 'DELETE']);
        $id = self::idDeLaUrl();

        if ($metodo === 'GET') {
            self::exigirRol($rolesLectura);
            if ($id !== null) {
                $fila = $modelo->obtenerPorId($id);
                if ($fila === null) {
                    self::error(404, "$etiqueta no encontrado.");
                }
                self::responder(200, ['status' => 'ok', 'datos' => $fila]);
            }
            $filas = $modelo->listar($_GET);
            self::responder(200, ['status' => 'ok', 'total' => count($filas), 'datos' => $filas]);
        }

        self::exigirRol($rolesEscritura);

        if ($metodo === 'POST') {
            $datos = self::entrada();
            $errores = $modelo->validar($datos);
            if (!empty($errores)) {
                self::error(400, 'Datos inválidos.', $errores);
            }
            $nuevoId = $modelo->crear($datos);
            if ($nuevoId === null) {
                self::errorDeModelo($modelo, $etiqueta);
            }
            self::responder(201, ['status' => 'ok', 'mensaje' => "$etiqueta registrado.", 'id' => $nuevoId]);
        }

        if ($id === null) {
            self::error(400, 'Debe indicar el id en la URL.');
        }
        if ($modelo->obtenerPorId($id) === null) {
            self::error(404, "$etiqueta no encontrado.");
        }

        if ($metodo === 'PUT') {
            $datos = self::entrada();
            $errores = $modelo->validar($datos);
            if (!empty($errores)) {
                self::error(400, 'Datos inválidos.', $errores);
            }
            if (!$modelo->actualizar($id, $datos)) {
                self::errorDeModelo($modelo, $etiqueta);
            }
            self::responder(200, ['status' => 'ok', 'mensaje' => "$etiqueta actualizado."]);
        }

        // DELETE
        if (!$modelo->eliminar($id)) {
            self::errorDeModelo($modelo, $etiqueta);
        }
        self::responder(200, ['status' => 'ok', 'mensaje' => "$etiqueta eliminado."]);
    }

    // Traduce el último error del modelo (duplicado / referenciado / genérico) a un código HTTP.
    private static function errorDeModelo(Recurso $modelo, string $etiqueta): void {
        switch ($modelo->ultimoError) {
            case 'duplicado':
                self::error(409, "Ya existe un $etiqueta con esos datos únicos (documento, matrícula o licencia).");
            case 'referenciado':
                self::error(409, "No se puede eliminar: el $etiqueta tiene traslados asociados.");
            default:
                self::error(500, 'Error en el servidor al procesar la operación.');
        }
    }
}

```

---

## Ruta: `backend\config\Auth.php`

```php
<?php

class Auth {
    
     //Inicia la sesión PHP con los parámetros de configuración de cookies seguras.
     
    public static function iniciarSesion(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start([
                'cookie_httponly' => true,
                'cookie_samesite' => 'Lax'
            ]);
        }
    }

    // Valida si existe un usuario autenticado en la sesión actual.
     
    public static function verificarAutenticacion(): bool {
        self::iniciarSesion();
        return isset($_SESSION['usuario']) && !empty($_SESSION['usuario']['id_usuario']);
    }

    
     //Valida que el rol del usuario conectado coincida con los permitidos para la operación.

    public static function verificarRol(array $rolesPermitidos): bool {
        if (!self::verificarAutenticacion()) {
            return false;
        }

        return in_array($_SESSION['usuario']['rol'], $rolesPermitidos, true);
    }
}

```

---

## Ruta: `backend\config\Database.php`

```php
<?php

class Database {
    // Credenciales por defecto apuntando al servidor MariaDB de CentOS Stream 9 (192.168.1.234:3306).
    // Se pueden sobreescribir con variables de entorno (DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS).
    private string $host;
    private string $port;
    private string $db_name;
    private string $username;
    private string $password;
    private ?PDO $conexion = null;

    public function __construct() {
        $this->host     = getenv('DB_HOST') ?: '192.168.1.234';
        $this->port     = getenv('DB_PORT') ?: '3306';
        $this->db_name  = getenv('DB_NAME') ?: 'sigsm_hospital';
        $this->username = getenv('DB_USER') ?: 'sigsm_app';
        $this->password = getenv('DB_PASS') ?: 'Hc1234_db';
    }

    // Obtiene y retorna la instancia activa de la conexión PDO.
    // Intenta primero el host principal (CentOS 192.168.1.234) y tiene respaldo automático a Docker local (sigsm_mariadb) y XAMPP (127.0.0.1).
    public function conectar(): ?PDO {
        $this->conexion = null;
        $hostsCandidatos = array_values(array_unique([$this->host, 'sigsm_mariadb', '127.0.0.1']));
        $ultimoError = null;

        foreach ($hostsCandidatos as $hostActual) {
            try {
                $dsn = "mysql:host={$hostActual};port={$this->port};dbname={$this->db_name};charset=utf8mb4";
                $opciones = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::ATTR_TIMEOUT            => 2,
                ];

                $this->conexion = new PDO($dsn, $this->username, $this->password, $opciones);
                return $this->conexion;
            } catch (PDOException $e) {
                $ultimoError = $e;
            }
        }

        // No se expone $ultimoError->getMessage() al cliente (puede filtrar host/usuario); se registra en el log.
        if ($ultimoError !== null) {
            error_log('Error de conexión a la base de datos: ' . $ultimoError->getMessage());
        }
        http_response_code(500);
        echo json_encode([
            'status'  => 'error',
            'mensaje' => 'Error de conexión a la base de datos.'
        ]);
        exit;
    }
}

```

---

## Ruta: `backend\models\Categoria.php`

```php
<?php

require_once __DIR__ . '/Recurso.php';

/**
 * Categoría institucional de documentos clínicos y encuestas de satisfacción.
 * Hereda el CRUD preparado de Recurso y mantiene listar() para compatibilidad con el portal.
 */
class Categoria extends Recurso {
    protected string $tabla = 'categoria';
    protected string $clavePrimaria = 'id_categoria';
    protected array $campos = ['nombre'];

    public function validar(array $datos): array {
        $errores = [];
        if (!$this->textoValido($datos['nombre'] ?? null, 100)) {
            $errores[] = 'El nombre de la categoría es obligatorio (máx. 100 caracteres).';
        }
        return $errores;
    }

    // Retorna el listado completo de las categorías ordenadas alfabéticamente.
    public function listar(array $filtros = []): array {
        $sql = "SELECT id_categoria, nombre FROM {$this->tabla} ORDER BY nombre ASC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

```

---

## Ruta: `backend\models\Chofer.php`

```php
<?php

require_once __DIR__ . '/Recurso.php';

// Chofer del hospital: puede manejar toda la flota (ambulancias y autos).
class Chofer extends Recurso {
    protected string $tabla = 'chofer';
    protected string $clavePrimaria = 'id_chofer';
    protected array $campos = ['nombre', 'telefono', 'licencia'];

    public function validar(array $datos): array {
        $errores = [];
        if (!$this->textoValido($datos['nombre'] ?? null, 100)) {
            $errores[] = 'El nombre es obligatorio (máx. 100 caracteres).';
        }
        if (!$this->telefonoValido($datos['telefono'] ?? null)) {
            $errores[] = 'El teléfono es obligatorio y solo admite números, +, espacios y guiones.';
        }
        if (!$this->textoValido($datos['licencia'] ?? null, 50)) {
            $errores[] = 'La licencia de conducir es obligatoria (máx. 50 caracteres).';
        }
        return $errores;
    }
}

```

---

## Ruta: `backend\models\Documento.php`

```php
<?php

require_once __DIR__ . '/../config/Database.php';

class Documento {
    private ?PDO $conexion;
    private string $tabla = 'documento';

    public ?int $id_documento = null;
    public ?string $titulo = null;
    public ?string $archivo = null;
    
    public ?int $id_usuario = null;
    public ?int $id_categoria = null;

    public function __construct() {
        $db = new Database();
        $this->conexion = $db->conectar();
    }

     //Obtiene todos los documentos vinculando nombre de categoría y autor
    public function listarTodos(?int $idCategoria = null): array {
        $sql = "SELECT 
                    d.id_documento,
                    d.titulo,
                    d.archivo,
                    
                    d.id_categoria,
                    c.nombre AS categoria_nombre,
                    u.nombre AS autor_nombre
                FROM {$this->tabla} d
                INNER JOIN categoria c ON d.id_categoria = c.id_categoria
                INNER JOIN usuario u ON d.id_usuario = u.id_usuario";

        if ($idCategoria !== null && $idCategoria > 0) {
            $sql .= " WHERE d.id_categoria = :id_categoria";
        }

        $sql .= " ORDER BY d.id_documento DESC";

        $stmt = $this->conexion->prepare($sql);
        if ($idCategoria !== null && $idCategoria > 0) {
            $stmt->bindParam(':id_categoria', $idCategoria, PDO::PARAM_INT);
        }
        $stmt->execute();

        return $stmt->fetchAll();
    }

    
    // Obtiene todos los documentos para  la visualización en el portal de pacientes
    public function listarPublicos(): array {
        $sql = "SELECT 
                    d.id_documento,
                    d.titulo,
                    d.archivo,
                    
                    d.id_categoria,
                    c.nombre AS categoria_nombre
                FROM {$this->tabla} d
                INNER JOIN categoria c ON d.id_categoria = c.id_categoria
                ORDER BY d.titulo ASC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    
     // Obtiene un documento específico por su id único
    public function obtenerPorId(int $id): ?array {
        $sql = "SELECT 
                    d.id_documento,
                    d.titulo,
                    d.archivo,
                    
                    d.id_categoria,
                    c.nombre AS categoria_nombre,
                    d.id_usuario
                FROM {$this->tabla} d
                INNER JOIN categoria c ON d.id_categoria = c.id_categoria
                WHERE d.id_documento = :id
                LIMIT 1";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $resultado = $stmt->fetch();
        return $resultado ?: null;
    }

    
    //Registra un nuevo documento clínico en la base de datos
    public function crear(array $datos): bool {
        $sql = "INSERT INTO {$this->tabla} 
 (titulo, archivo, id_usuario, id_categoria) 
 VALUES 
 (:titulo, :archivo, :id_usuario, :id_categoria)";

        $stmt = $this->conexion->prepare($sql);

        $stmt->bindParam(':titulo', $datos['titulo'], PDO::PARAM_STR);
        $stmt->bindParam(':archivo', $datos['archivo'], PDO::PARAM_STR);
        
        $stmt->bindParam(':id_usuario', $datos['id_usuario'], PDO::PARAM_INT);
        $stmt->bindParam(':id_categoria', $datos['id_categoria'], PDO::PARAM_INT);

        return $stmt->execute();
    }

    
     //Actualiza los metadatos y opcionalmente el archivo de un documento existente
    public function actualizar(int $id, array $datos): bool {
        $docActual = $this->obtenerPorId($id);
        if (!$docActual) {
            return false;
        }

        $actualizarArchivo = !empty($datos['archivo']);
        $sql = "UPDATE {$this->tabla} 
 SET titulo = :titulo, 
     id_categoria = :id_categoria";

        if ($actualizarArchivo) {
            $sql .= ", archivo = :archivo";
        }

        $sql .= " WHERE id_documento = :id";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':titulo', $datos['titulo'], PDO::PARAM_STR);
        $stmt->bindParam(':id_categoria', $datos['id_categoria'], PDO::PARAM_INT);
        
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($actualizarArchivo) {
            $stmt->bindParam(':archivo', $datos['archivo'], PDO::PARAM_STR);
        }

        $ejecutado = $stmt->execute();

        // Limpieza física del archivo anterior si el update fue exitoso
        if ($ejecutado && $actualizarArchivo && !empty($docActual['archivo'])) {
            $rutaAnterior = __DIR__ . '/../../' . ltrim($docActual['archivo'], '/');
            if (file_exists($rutaAnterior)) {
                unlink($rutaAnterior);
            }
        }

        return $ejecutado;
    }

    // Elimina el registro del documento
    public function eliminar(int $id): bool {
        $doc = $this->obtenerPorId($id);
        if (!$doc) {
            return false;
        }

        $sql = "DELETE FROM {$this->tabla} WHERE id_documento = :id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        $eliminado = $stmt->execute();

        // Si se eliminó de la BD, borrar el archivo físico del servidor
        if ($eliminado && !empty($doc['archivo'])) {
            $rutaFisica = __DIR__ . '/../../' . ltrim($doc['archivo'], '/');
            if (file_exists($rutaFisica)) {
                unlink($rutaFisica);
            }
        }

        return $eliminado;
    }
}

```

---

## Ruta: `backend\models\Encuesta.php`

```php
<?php

require_once __DIR__ . '/../config/Database.php';

/**
 * Encuesta de satisfacción anónima vinculada a una categoría clínica.
 * Implementa las restricciones RNE08, RNE09 y RNE10 en guardarResolucion()
 * y expone el resumen estadístico básico para el panel administrativo.
 */
class Encuesta {
    private ?PDO $conexion;
    private string $tabla = 'encuesta';

    /** Mensaje de error de validación (RNE08 / RNE09 / RNE10) de la última resolución. */
    public string $ultimoError = '';
    public int $codigoError = 400;

    public function __construct() {
        $db = new Database();
        $this->conexion = $db->conectar();
    }

    // Obtiene la encuesta activa de una categoría específica, con sus preguntas y opciones.
    public function obtenerActivaPorCategoria(int $id_categoria): ?array {
        $sql = "SELECT id_encuesta, comentarios, id_categoria 
                FROM {$this->tabla} 
                WHERE id_categoria = :id_categoria AND estado = 'Activa' 
                ORDER BY id_encuesta DESC
                LIMIT 1";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id_categoria', $id_categoria, PDO::PARAM_INT);
        $stmt->execute();

        $encuesta = $stmt->fetch();
        if (!$encuesta) {
            return null;
        }

        return $this->cargarPreguntasDeEncuesta($encuesta);
    }

    // Obtiene una encuesta activa específica por su ID, con sus preguntas y opciones.
    public function obtenerActivaPorId(int $id_encuesta): ?array {
        $sql = "SELECT e.id_encuesta, e.comentarios, e.id_categoria, c.nombre AS categoria_nombre
                FROM {$this->tabla} e
                INNER JOIN categoria c ON c.id_categoria = e.id_categoria
                WHERE e.id_encuesta = :id_encuesta AND e.estado = 'Activa'
                LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':id_encuesta', $id_encuesta, PDO::PARAM_INT);
        $stmt->execute();

        $encuesta = $stmt->fetch();
        if (!$encuesta) {
            return null;
        }

        return $this->cargarPreguntasDeEncuesta($encuesta);
    }

    // Lista todas las encuestas activas (con cantidad de preguntas) para el Portal de Invitados.
    public function listarActivasPublicas(): array {
        $sql = "SELECT e.id_encuesta, e.comentarios, e.id_categoria, c.nombre AS categoria_nombre,
                       (SELECT COUNT(*) FROM pregunta p WHERE p.id_encuesta = e.id_encuesta) AS total_preguntas
                FROM {$this->tabla} e
                INNER JOIN categoria c ON c.id_categoria = e.id_categoria
                WHERE e.estado = 'Activa'
                ORDER BY e.id_encuesta DESC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function cargarPreguntasDeEncuesta(array $encuesta): array {
        $id_encuesta = (int)$encuesta['id_encuesta'];

        // Obtener preguntas de la encuesta
        $sqlPreguntas = "SELECT id_pregunta, enunciado, tipo 
                         FROM pregunta 
                         WHERE id_encuesta = :id_encuesta
                         ORDER BY id_pregunta ASC";
        $stmtPreguntas = $this->conexion->prepare($sqlPreguntas);
        $stmtPreguntas->bindParam(':id_encuesta', $id_encuesta, PDO::PARAM_INT);
        $stmtPreguntas->execute();
        $preguntas = $stmtPreguntas->fetchAll();

        // Para cada pregunta de tipo Opcion_Multiple, obtener sus opciones
        foreach ($preguntas as &$pregunta) {
            $pregunta['id_pregunta'] = (int)$pregunta['id_pregunta'];
            if ($pregunta['tipo'] === 'Opcion_Multiple') {
                $sqlOpciones = "SELECT id_opcion, texto_opcion 
                                FROM opcion_pregunta 
                                WHERE id_pregunta = :id_pregunta
                                ORDER BY id_opcion ASC";
                $stmtOpciones = $this->conexion->prepare($sqlOpciones);
                $stmtOpciones->bindParam(':id_pregunta', $pregunta['id_pregunta'], PDO::PARAM_INT);
                $stmtOpciones->execute();
                $pregunta['opciones'] = $stmtOpciones->fetchAll();
            } else {
                $pregunta['opciones'] = [];
            }
        }

        $encuesta['id_encuesta'] = $id_encuesta;
        $encuesta['preguntas'] = $preguntas;

        return $encuesta;
    }

    /**
     * Guarda de forma anónima y transaccional una resolución de encuesta con sus respuestas.
     * Valida:
     *   - RNE08: la encuesta debe existir y tener estado = 'Activa'.
     *   - RNE09: cada id_pregunta debe pertenecer a esa misma id_encuesta.
     *   - RNE10: dominio de respuesta según pregunta.tipo (Si/No -> SI/NO, Escala_1_5 -> 1..5).
     */
    public function guardarResolucion(int $id_encuesta, string $observaciones, array $respuestas): bool {
        $this->ultimoError = '';
        $this->codigoError = 400;

        // RNE08: verificar que la encuesta exista y esté Activa
        $stmtEnc = $this->conexion->prepare(
            "SELECT id_encuesta, estado FROM encuesta WHERE id_encuesta = :id LIMIT 1"
        );
        $stmtEnc->bindValue(':id', $id_encuesta, PDO::PARAM_INT);
        $stmtEnc->execute();
        $encuesta = $stmtEnc->fetch();

        if (!$encuesta) {
            $this->ultimoError = 'La encuesta indicada no existe.';
            $this->codigoError = 404;
            return false;
        }
        if ($encuesta['estado'] !== 'Activa') {
            $this->ultimoError = 'Solo se admiten respuestas para encuestas que se encuentren activas.';
            $this->codigoError = 409;
            return false;
        }

        // Cargar preguntas válidas de esta encuesta para verificar RNE09 y RNE10
        $stmtPregs = $this->conexion->prepare(
            "SELECT id_pregunta, tipo FROM pregunta WHERE id_encuesta = :id"
        );
        $stmtPregs->bindValue(':id', $id_encuesta, PDO::PARAM_INT);
        $stmtPregs->execute();
        $mapaPreguntas = [];
        foreach ($stmtPregs->fetchAll() as $p) {
            $mapaPreguntas[(int)$p['id_pregunta']] = $p['tipo'];
        }

        $respuestasLimpias = [];
        foreach ($respuestas as $resp) {
            $id_pregunta = (int)($resp['id_pregunta'] ?? 0);
            $contenido = trim((string)($resp['contenido_respuesta'] ?? ''));

            // RNE09: la pregunta debe pertenecer a la encuesta
            if (!isset($mapaPreguntas[$id_pregunta])) {
                $this->ultimoError = "La pregunta #$id_pregunta no pertenece a la encuesta seleccionada.";
                return false;
            }

            $tipo = $mapaPreguntas[$id_pregunta];

            if ($contenido === '') {
                if ($tipo === 'Texto Libre') {
                    continue;
                }
                $this->ultimoError = 'Debe responder todas las preguntas de opción o calificación.';
                return false;
            }

            // RNE10: validación de dominio según el tipo de pregunta
            if ($tipo === 'Si/No') {
                $upper = strtr(mb_strtoupper($contenido, 'UTF-8'), ['Í' => 'I']);
                if ($upper !== 'SI' && $upper !== 'NO') {
                    $this->ultimoError = "Para preguntas de tipo Sí/No el valor solo puede ser 'Sí' o 'No'.";
                    return false;
                }
                $contenido = ($upper === 'SI') ? 'Sí' : 'No';
            } elseif ($tipo === 'Escala_1_5') {
                if (!in_array($contenido, ['1', '2', '3', '4', '5'], true)) {
                    $this->ultimoError = 'La calificación en escala debe ser un número entero entre 1 y 5.';
                    return false;
                }
            } elseif ($tipo === 'Opcion_Multiple') {
                $stmtOps = $this->conexion->prepare(
                    "SELECT texto_opcion FROM opcion_pregunta WHERE id_pregunta = :id_pregunta"
                );
                $stmtOps->execute(['id_pregunta' => $id_pregunta]);
                $opcionesValidas = $stmtOps->fetchAll(PDO::FETCH_COLUMN);
                if (!in_array($contenido, $opcionesValidas, true)) {
                    $this->ultimoError = 'La opción seleccionada no pertenece a las opciones válidas de la pregunta.';
                    return false;
                }
            }

            $respuestasLimpias[] = [
                'id_pregunta' => $id_pregunta,
                'contenido'   => $contenido
            ];
        }

        if (empty($respuestasLimpias)) {
            $this->ultimoError = 'Debe responder al menos una pregunta de la encuesta.';
            return false;
        }

        try {
            $this->conexion->beginTransaction();

            $sqlResolucion = "INSERT INTO resolucion_encuesta (id_encuesta, observaciones) 
                              VALUES (:id_encuesta, :observaciones)";
            $stmtRes = $this->conexion->prepare($sqlResolucion);
            $stmtRes->bindValue(':id_encuesta', $id_encuesta, PDO::PARAM_INT);
            $stmtRes->bindValue(':observaciones', $observaciones !== '' ? $observaciones : null, PDO::PARAM_STR);
            $stmtRes->execute();

            $id_resolucion = (int)$this->conexion->lastInsertId();

            $sqlRespuesta = "INSERT INTO respuesta (contenido_respuesta, id_resolucion, id_pregunta) 
                             VALUES (:contenido, :id_resolucion, :id_pregunta)";
            $stmtResp = $this->conexion->prepare($sqlRespuesta);

            foreach ($respuestasLimpias as $item) {
                $stmtResp->bindValue(':contenido', $item['contenido'], PDO::PARAM_STR);
                $stmtResp->bindValue(':id_resolucion', $id_resolucion, PDO::PARAM_INT);
                $stmtResp->bindValue(':id_pregunta', $item['id_pregunta'], PDO::PARAM_INT);
                $stmtResp->execute();
            }

            $this->conexion->commit();
            return true;
        } catch (Exception $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            error_log('Error al guardar resolucion: ' . $e->getMessage());
            $this->ultimoError = 'Error interno al almacenar las respuestas.';
            $this->codigoError = 500;
            return false;
        }
    }

    /**
     * Obtiene el resumen de resultados de las encuestas de satisfacción (opcionalmente filtrado por categoría):
     * cantidad de resoluciones, desglose por pregunta (conteo Sí/No, promedio y distribución 1-5, textos libres)
     * y observaciones generales registradas.
     */
    public function obtenerResultados(?int $id_categoria = null): array {
        $sql = "SELECT e.id_encuesta, e.comentarios, e.estado, e.id_categoria, c.nombre AS categoria_nombre
                FROM encuesta e
                INNER JOIN categoria c ON c.id_categoria = e.id_categoria";
        if ($id_categoria !== null && $id_categoria > 0) {
            $sql .= " WHERE e.id_categoria = :id_categoria";
        }
        $sql .= " ORDER BY e.id_encuesta DESC";

        $stmt = $this->conexion->prepare($sql);
        if ($id_categoria !== null && $id_categoria > 0) {
            $stmt->bindValue(':id_categoria', $id_categoria, PDO::PARAM_INT);
        }
        $stmt->execute();
        $encuestas = $stmt->fetchAll();

        $stmtResoluciones = $this->conexion->prepare(
            "SELECT id_resolucion, fecha_hora, observaciones
             FROM resolucion_encuesta
             WHERE id_encuesta = :id_encuesta
             ORDER BY fecha_hora DESC"
        );
        $stmtPreguntas = $this->conexion->prepare(
            "SELECT id_pregunta, enunciado, tipo
             FROM pregunta
             WHERE id_encuesta = :id_encuesta
             ORDER BY id_pregunta ASC"
        );
        $stmtRespuestas = $this->conexion->prepare(
            "SELECT contenido_respuesta
             FROM respuesta
             WHERE id_pregunta = :id_pregunta
             ORDER BY id_respuesta DESC"
        );

        $resultado = [];
        foreach ($encuestas as $enc) {
            $idEnc = (int)$enc['id_encuesta'];

            $stmtResoluciones->execute(['id_encuesta' => $idEnc]);
            $resoluciones = $stmtResoluciones->fetchAll();

            $observaciones = [];
            foreach ($resoluciones as $res) {
                if ($res['observaciones'] !== null && trim($res['observaciones']) !== '') {
                    $observaciones[] = [
                        'fecha_hora' => $res['fecha_hora'],
                        'texto'      => $res['observaciones']
                    ];
                }
            }

            $stmtPreguntas->execute(['id_encuesta' => $idEnc]);
            $preguntas = $stmtPreguntas->fetchAll();
            $preguntasFormateadas = [];

            foreach ($preguntas as $preg) {
                $idPreg = (int)$preg['id_pregunta'];
                $stmtRespuestas->execute(['id_pregunta' => $idPreg]);
                $valores = $stmtRespuestas->fetchAll(PDO::FETCH_COLUMN);

                $resumenPregunta = [
                    'id_pregunta'      => $idPreg,
                    'enunciado'        => $preg['enunciado'],
                    'tipo'             => $preg['tipo'],
                    'total_respuestas' => count($valores)
                ];

                if ($preg['tipo'] === 'Si/No') {
                    $si = 0;
                    $no = 0;
                    foreach ($valores as $v) {
                        $norm = strtr(mb_strtoupper(trim((string)$v), 'UTF-8'), ['Í' => 'I']);
                        if ($norm === 'SI') {
                            $si++;
                        } elseif ($norm === 'NO') {
                            $no++;
                        }
                    }
                    $resumenPregunta['si'] = $si;
                    $resumenPregunta['no'] = $no;
                } elseif ($preg['tipo'] === 'Escala_1_5') {
                    $distribucion = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
                    $suma = 0;
                    $conteoValido = 0;
                    foreach ($valores as $v) {
                        $num = (int)$v;
                        if ($num >= 1 && $num <= 5) {
                            $distribucion[$num]++;
                            $suma += $num;
                            $conteoValido++;
                        }
                    }
                    $resumenPregunta['promedio'] = $conteoValido > 0 ? round($suma / $conteoValido, 2) : null;
                    $resumenPregunta['distribucion'] = $distribucion;
                } elseif ($preg['tipo'] === 'Opcion_Multiple') {
                    $stmtOps = $this->conexion->prepare(
                        "SELECT texto_opcion FROM opcion_pregunta WHERE id_pregunta = :id_pregunta ORDER BY id_opcion ASC"
                    );
                    $stmtOps->execute(['id_pregunta' => $idPreg]);
                    $opciones = $stmtOps->fetchAll(PDO::FETCH_COLUMN);
                    $conteoOpciones = [];
                    foreach ($opciones as $op) {
                        $conteoOpciones[$op] = 0;
                    }
                    foreach ($valores as $v) {
                        $txt = trim((string)$v);
                        if ($txt !== '') {
                            $conteoOpciones[$txt] = ($conteoOpciones[$txt] ?? 0) + 1;
                        }
                    }
                    $resumenPregunta['opciones'] = $opciones;
                    $resumenPregunta['conteo_opciones'] = $conteoOpciones;
                } else {
                    $resumenPregunta['respuestas'] = array_values(array_filter(
                        array_map('trim', $valores),
                        fn($txt) => $txt !== ''
                    ));
                }

                $preguntasFormateadas[] = $resumenPregunta;
            }

            $resultado[] = [
                'id_encuesta'        => $idEnc,
                'id_categoria'       => (int)$enc['id_categoria'],
                'categoria_nombre'   => $enc['categoria_nombre'],
                'comentarios'        => $enc['comentarios'],
                'estado'             => $enc['estado'],
                'total_resoluciones' => count($resoluciones),
                'preguntas'          => $preguntasFormateadas,
                'observaciones'      => $observaciones
            ];
        }

        return $resultado;
    }

    // ---------------------------------------------------------------- Gestión de Encuestas y Preguntas (CRUD)

    public function obtenerEncuestaPorId(int $id): ?array {
        $stmt = $this->conexion->prepare("SELECT * FROM encuesta WHERE id_encuesta = :id LIMIT 1");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    // Crea una encuesta para una categoría (si pasa a Activa, cierra las anteriores de esa misma categoría).
    public function crearEncuesta(int $id_categoria, string $comentarios, string $estado = 'Activa'): ?int {
        $this->ultimoError = '';
        $this->codigoError = 400;

        if ($id_categoria <= 0 || trim($comentarios) === '') {
            $this->ultimoError = 'La categoría y el título de la encuesta son obligatorios.';
            return null;
        }
        if (!in_array($estado, ['Borrador', 'Activa', 'Cerrada'], true)) {
            $this->ultimoError = 'El estado de la encuesta no es válido.';
            return null;
        }

        try {
            $this->conexion->beginTransaction();

            // Si la nueva encuesta queda Activa, se cierran otras encuestas activas de esa misma categoría
            if ($estado === 'Activa') {
                $stmtCerrar = $this->conexion->prepare(
                    "UPDATE encuesta SET estado = 'Cerrada' WHERE id_categoria = :id_categoria AND estado = 'Activa'"
                );
                $stmtCerrar->bindValue(':id_categoria', $id_categoria, PDO::PARAM_INT);
                $stmtCerrar->execute();
            }

            $stmt = $this->conexion->prepare(
                "INSERT INTO encuesta (comentarios, estado, id_categoria) VALUES (:comentarios, :estado, :id_categoria)"
            );
            $stmt->bindValue(':comentarios', trim($comentarios), PDO::PARAM_STR);
            $stmt->bindValue(':estado', $estado, PDO::PARAM_STR);
            $stmt->bindValue(':id_categoria', $id_categoria, PDO::PARAM_INT);
            $stmt->execute();
            $idNueva = (int)$this->conexion->lastInsertId();

            $this->conexion->commit();
            return $idNueva;
        } catch (Exception $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            $this->ultimoError = 'Error al crear la encuesta.';
            $this->codigoError = 500;
            return null;
        }
    }

    // Actualiza el comentario/título y el estado ('Borrador', 'Activa', 'Cerrada') de una encuesta.
    public function actualizarEncuesta(int $id_encuesta, string $comentarios, string $estado): bool {
        $this->ultimoError = '';
        $this->codigoError = 400;

        $actual = $this->obtenerEncuestaPorId($id_encuesta);
        if ($actual === null) {
            $this->ultimoError = 'Encuesta no encontrada.';
            $this->codigoError = 404;
            return false;
        }
        if (trim($comentarios) === '' || !in_array($estado, ['Borrador', 'Activa', 'Cerrada'], true)) {
            $this->ultimoError = 'El título y un estado válido (Borrador, Activa o Cerrada) son obligatorios.';
            return false;
        }

        try {
            $this->conexion->beginTransaction();

            // Si pasa a Activa, cerramos cualquier otra encuesta activa de esa misma categoría
            if ($estado === 'Activa') {
                $stmtCerrar = $this->conexion->prepare(
                    "UPDATE encuesta SET estado = 'Cerrada'
                     WHERE id_categoria = :id_categoria AND id_encuesta <> :id_encuesta AND estado = 'Activa'"
                );
                $stmtCerrar->bindValue(':id_categoria', (int)$actual['id_categoria'], PDO::PARAM_INT);
                $stmtCerrar->bindValue(':id_encuesta', $id_encuesta, PDO::PARAM_INT);
                $stmtCerrar->execute();
            }

            $stmt = $this->conexion->prepare(
                "UPDATE encuesta SET comentarios = :comentarios, estado = :estado WHERE id_encuesta = :id"
            );
            $stmt->bindValue(':comentarios', trim($comentarios), PDO::PARAM_STR);
            $stmt->bindValue(':estado', $estado, PDO::PARAM_STR);
            $stmt->bindValue(':id', $id_encuesta, PDO::PARAM_INT);
            $stmt->execute();

            $this->conexion->commit();
            return true;
        } catch (Exception $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            $this->ultimoError = 'Error al actualizar la encuesta.';
            $this->codigoError = 500;
            return false;
        }
    }

    // Elimina una encuesta completa (preguntas, opciones y resoluciones se eliminan en cascada según FK).
    public function eliminarEncuesta(int $id_encuesta): bool {
        $this->ultimoError = '';
        if ($this->obtenerEncuestaPorId($id_encuesta) === null) {
            $this->ultimoError = 'Encuesta no encontrada.';
            $this->codigoError = 404;
            return false;
        }
        $stmt = $this->conexion->prepare("DELETE FROM encuesta WHERE id_encuesta = :id");
        $stmt->bindValue(':id', $id_encuesta, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function obtenerPreguntaPorId(int $id_pregunta): ?array {
        $stmt = $this->conexion->prepare("SELECT * FROM pregunta WHERE id_pregunta = :id LIMIT 1");
        $stmt->bindValue(':id', $id_pregunta, PDO::PARAM_INT);
        $stmt->execute();
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    // Crea una pregunta dentro de una encuesta (y sus opciones si es Opcion_Multiple).
    public function crearPregunta(int $id_encuesta, string $enunciado, string $tipo, array $opciones = []): ?int {
        $this->ultimoError = '';
        $this->codigoError = 400;

        if ($this->obtenerEncuestaPorId($id_encuesta) === null) {
            $this->ultimoError = 'La encuesta indicada no existe.';
            $this->codigoError = 404;
            return null;
        }
        $enunciado = trim($enunciado);
        if ($enunciado === '' || !in_array($tipo, ['Si/No', 'Texto Libre', 'Escala_1_5', 'Opcion_Multiple'], true)) {
            $this->ultimoError = 'El enunciado y un tipo de pregunta válido son obligatorios.';
            return null;
        }
        $opcionesLimpias = array_values(array_filter(array_map('trim', $opciones), fn($x) => $x !== ''));
        if ($tipo === 'Opcion_Multiple' && count($opcionesLimpias) < 2) {
            $this->ultimoError = 'Una pregunta de Opción Múltiple requiere al menos 2 opciones.';
            return null;
        }

        try {
            $this->conexion->beginTransaction();

            $stmt = $this->conexion->prepare(
                "INSERT INTO pregunta (enunciado, tipo, id_encuesta) VALUES (:enunciado, :tipo, :id_encuesta)"
            );
            $stmt->bindValue(':enunciado', $enunciado, PDO::PARAM_STR);
            $stmt->bindValue(':tipo', $tipo, PDO::PARAM_STR);
            $stmt->bindValue(':id_encuesta', $id_encuesta, PDO::PARAM_INT);
            $stmt->execute();
            $id_pregunta = (int)$this->conexion->lastInsertId();

            if ($tipo === 'Opcion_Multiple') {
                $stmtOp = $this->conexion->prepare(
                    "INSERT INTO opcion_pregunta (texto_opcion, id_pregunta) VALUES (:texto, :id_pregunta)"
                );
                foreach ($opcionesLimpias as $texto) {
                    $stmtOp->bindValue(':texto', $texto, PDO::PARAM_STR);
                    $stmtOp->bindValue(':id_pregunta', $id_pregunta, PDO::PARAM_INT);
                    $stmtOp->execute();
                }
            }

            $this->conexion->commit();
            return $id_pregunta;
        } catch (Exception $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            $this->ultimoError = 'Error al registrar la pregunta.';
            $this->codigoError = 500;
            return null;
        }
    }

    // Actualiza el enunciado y el tipo de una pregunta existente.
    public function actualizarPregunta(int $id_pregunta, string $enunciado, string $tipo, array $opciones = []): bool {
        $this->ultimoError = '';
        $this->codigoError = 400;

        if ($this->obtenerPreguntaPorId($id_pregunta) === null) {
            $this->ultimoError = 'Pregunta no encontrada.';
            $this->codigoError = 404;
            return false;
        }
        $enunciado = trim($enunciado);
        if ($enunciado === '' || !in_array($tipo, ['Si/No', 'Texto Libre', 'Escala_1_5', 'Opcion_Multiple'], true)) {
            $this->ultimoError = 'El enunciado y un tipo de pregunta válido son obligatorios.';
            return false;
        }
        $opcionesLimpias = array_values(array_filter(array_map('trim', $opciones), fn($x) => $x !== ''));
        if ($tipo === 'Opcion_Multiple' && count($opcionesLimpias) < 2) {
            $this->ultimoError = 'Una pregunta de Opción Múltiple requiere al menos 2 opciones.';
            return false;
        }

        try {
            $this->conexion->beginTransaction();

            $stmt = $this->conexion->prepare(
                "UPDATE pregunta SET enunciado = :enunciado, tipo = :tipo WHERE id_pregunta = :id"
            );
            $stmt->bindValue(':enunciado', $enunciado, PDO::PARAM_STR);
            $stmt->bindValue(':tipo', $tipo, PDO::PARAM_STR);
            $stmt->bindValue(':id', $id_pregunta, PDO::PARAM_INT);
            $stmt->execute();

            $stmtDel = $this->conexion->prepare("DELETE FROM opcion_pregunta WHERE id_pregunta = :id");
            $stmtDel->bindValue(':id', $id_pregunta, PDO::PARAM_INT);
            $stmtDel->execute();

            if ($tipo === 'Opcion_Multiple') {
                $stmtOp = $this->conexion->prepare(
                    "INSERT INTO opcion_pregunta (texto_opcion, id_pregunta) VALUES (:texto, :id_pregunta)"
                );
                foreach ($opcionesLimpias as $texto) {
                    $stmtOp->bindValue(':texto', $texto, PDO::PARAM_STR);
                    $stmtOp->bindValue(':id_pregunta', $id_pregunta, PDO::PARAM_INT);
                    $stmtOp->execute();
                }
            }

            $this->conexion->commit();
            return true;
        } catch (Exception $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            $this->ultimoError = 'Error al actualizar la pregunta.';
            $this->codigoError = 500;
            return false;
        }
    }

    // Elimina una pregunta (sus respuestas y opciones se eliminan en cascada según FK ON DELETE CASCADE).
    public function eliminarPregunta(int $id_pregunta): bool {
        $this->ultimoError = '';
        if ($this->obtenerPreguntaPorId($id_pregunta) === null) {
            $this->ultimoError = 'Pregunta no encontrada.';
            $this->codigoError = 404;
            return false;
        }
        $stmt = $this->conexion->prepare("DELETE FROM pregunta WHERE id_pregunta = :id");
        $stmt->bindValue(':id', $id_pregunta, PDO::PARAM_INT);
        return $stmt->execute();
    }
}

```

---

## Ruta: `backend\models\Enfermero.php`

```php
<?php

require_once __DIR__ . '/Recurso.php';

// Enfermero que viaja con el chofer: es el único responsable del paciente durante el traslado.
class Enfermero extends Recurso {
    protected string $tabla = 'enfermero';
    protected string $clavePrimaria = 'id_enfermero';
    protected array $campos = ['nombre', 'telefono', 'matricula_profesional'];

    public function validar(array $datos): array {
        $errores = [];
        if (!$this->textoValido($datos['nombre'] ?? null, 100)) {
            $errores[] = 'El nombre es obligatorio (máx. 100 caracteres).';
        }
        if (!$this->telefonoValido($datos['telefono'] ?? null)) {
            $errores[] = 'El teléfono es obligatorio y solo admite números, +, espacios y guiones.';
        }
        if (!$this->textoValido($datos['matricula_profesional'] ?? null, 50)) {
            $errores[] = 'La matrícula profesional es obligatoria (máx. 50 caracteres).';
        }
        return $errores;
    }
}

```

---

## Ruta: `backend\models\Paciente.php`

```php
<?php

require_once __DIR__ . '/Recurso.php';

/**
 * Paciente a trasladar. El ingreso de datos es manual (mínimo exigido por el profesor);
 * la consulta a una API externa por C.I. queda como mejora opcional.
 */
class Paciente extends Recurso {
    protected string $tabla = 'paciente';
    protected string $clavePrimaria = 'id_paciente';
    protected array $campos = [
        'documento', 'nombre', 'telefono', 'fecha_de_nacimiento',
        'requiere_oxigeno', 'requiere_camilla', 'requiere_aislamiento', 'se_maneja_por_sus_medios'
    ];

    public function validar(array $datos): array {
        $errores = [];
        if (!isset($datos['documento']) || preg_match('/^[0-9]{6,12}$/', trim((string)$datos['documento'])) !== 1) {
            $errores[] = 'El documento (C.I.) es obligatorio y debe tener entre 6 y 12 dígitos, sin puntos ni guiones.';
        }
        if (!$this->textoValido($datos['nombre'] ?? null, 100)) {
            $errores[] = 'El nombre del paciente es obligatorio (máx. 100 caracteres).';
        }
        if (!empty($datos['telefono']) && !$this->telefonoValido($datos['telefono'])) {
            $errores[] = 'El teléfono solo admite números, +, espacios y guiones.';
        }
        $fecha = $datos['fecha_de_nacimiento'] ?? '';
        $fechaObj = DateTime::createFromFormat('Y-m-d', is_string($fecha) ? $fecha : '');
        if (!$fechaObj || $fechaObj->format('Y-m-d') !== $fecha || $fechaObj > new DateTime('today')) {
            $errores[] = 'La fecha de nacimiento es obligatoria, con formato AAAA-MM-DD y no puede ser futura.';
        }
        return $errores;
    }

    // Filtra por ?documento= (búsqueda exacta por C.I., usada en el formulario de alta de traslado).
    public function listar(array $filtros = []): array {
        if (!empty($filtros['documento'])) {
            $sql = "SELECT * FROM paciente WHERE documento = :documento";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindValue(':documento', trim((string)$filtros['documento']), PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll();
        }
        return parent::listar($filtros);
    }

    public function obtenerPorDocumento(string $documento): ?array {
        $sql = "SELECT * FROM paciente WHERE documento = :documento LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':documento', $documento, PDO::PARAM_STR);
        $stmt->execute();
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    /**
     * Alta manual con "upsert": si ya existe un paciente con ese documento actualiza sus datos
     * y reutiliza el registro; si no, lo crea. Devuelve el id del paciente.
     */
    public function crear(array $datos): ?int {
        $datos['documento'] = trim((string)($datos['documento'] ?? ''));
        foreach (['requiere_oxigeno', 'requiere_camilla', 'requiere_aislamiento'] as $campo) {
            $datos[$campo] = empty($datos[$campo]) ? 0 : 1;
        }
        // Por defecto el paciente se maneja por sus medios (igual que el DEFAULT 1 de la tabla).
        $datos['se_maneja_por_sus_medios'] = (!isset($datos['se_maneja_por_sus_medios']) || $datos['se_maneja_por_sus_medios']) ? 1 : 0;

        $existente = $this->obtenerPorDocumento($datos['documento']);
        if ($existente !== null) {
            $id = (int)$existente['id_paciente'];
            return $this->actualizar($id, $datos) ? $id : null;
        }
        return parent::crear($datos);
    }

    public function actualizar(int $id, array $datos): bool {
        foreach (['requiere_oxigeno', 'requiere_camilla', 'requiere_aislamiento'] as $campo) {
            $datos[$campo] = empty($datos[$campo]) ? 0 : 1;
        }
        $datos['se_maneja_por_sus_medios'] = (!isset($datos['se_maneja_por_sus_medios']) || $datos['se_maneja_por_sus_medios']) ? 1 : 0;
        return parent::actualizar($id, $datos);
    }
}

```

---

## Ruta: `backend\models\Recurso.php`

```php
<?php

require_once __DIR__ . '/../config/Database.php';

/**
 * Clase base (capa de negocio + acceso a datos) para las entidades simples del módulo
 * Ambulancias: Chofer, Enfermero, Vehiculo y Paciente.
 * Las subclases solo declaran la tabla, la clave primaria, los campos y sus reglas de validación.
 * Todas las consultas son preparadas (PDO) para prevenir inyección SQL.
 */
abstract class Recurso {
    protected ?PDO $conexion;
    protected string $tabla;
    protected string $clavePrimaria;
    /** @var string[] Columnas editables (lista blanca: solo estas llegan a la base). */
    protected array $campos = [];

    /** Último error ocurrido: '', 'duplicado', 'referenciado' o 'error'. */
    public string $ultimoError = '';

    public function __construct() {
        $db = new Database();
        $this->conexion = $db->conectar();
    }

    // Devuelve la lista de mensajes de error de validación (vacía si los datos son correctos).
    abstract public function validar(array $datos): array;

    // Retorna todos los registros. Las subclases pueden usar $filtros (ej: ?documento=).
    public function listar(array $filtros = []): array {
        $sql = "SELECT * FROM {$this->tabla} ORDER BY {$this->clavePrimaria} ASC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): ?array {
        $sql = "SELECT * FROM {$this->tabla} WHERE {$this->clavePrimaria} = :id LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    // Inserta un registro y devuelve su id (null si falla; ver $ultimoError).
    public function crear(array $datos): ?int {
        $this->ultimoError = '';
        $columnas = implode(', ', $this->campos);
        $marcas = implode(', ', array_map(fn($c) => ':' . $c, $this->campos));
        $sql = "INSERT INTO {$this->tabla} ($columnas) VALUES ($marcas)";

        try {
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute($this->limpiar($datos));
            return (int)$this->conexion->lastInsertId();
        } catch (PDOException $e) {
            $this->registrarError($e);
            return null;
        }
    }

    public function actualizar(int $id, array $datos): bool {
        $this->ultimoError = '';
        $asignaciones = implode(', ', array_map(fn($c) => "$c = :$c", $this->campos));
        $sql = "UPDATE {$this->tabla} SET $asignaciones WHERE {$this->clavePrimaria} = :id_registro";

        try {
            $stmt = $this->conexion->prepare($sql);
            $parametros = $this->limpiar($datos);
            $parametros['id_registro'] = $id;
            $stmt->execute($parametros);
            return true;
        } catch (PDOException $e) {
            $this->registrarError($e);
            return false;
        }
    }

    // Elimina un registro. Si tiene traslados asociados la FK (RESTRICT) lo impide.
    public function eliminar(int $id): bool {
        $this->ultimoError = '';
        $sql = "DELETE FROM {$this->tabla} WHERE {$this->clavePrimaria} = :id";

        try {
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            $this->registrarError($e);
            return false;
        }
    }

    // Deja solo los campos permitidos, con trim y '' convertido a null.
    protected function limpiar(array $datos): array {
        $limpio = [];
        foreach ($this->campos as $campo) {
            $valor = $datos[$campo] ?? null;
            if (is_string($valor)) {
                $valor = trim($valor);
                $valor = ($valor === '') ? null : $valor;
            }
            $limpio[$campo] = $valor;
        }
        return $limpio;
    }

    // Clasifica el error de PDO: 1062 = clave única duplicada, 1451 = referenciado por una FK.
    protected function registrarError(PDOException $e): void {
        $codigoMotor = $e->errorInfo[1] ?? 0;
        if ($codigoMotor === 1062) {
            $this->ultimoError = 'duplicado';
        } elseif ($codigoMotor === 1451) {
            $this->ultimoError = 'referenciado';
        } else {
            $this->ultimoError = 'error';
            error_log('Error en ' . static::class . ': ' . $e->getMessage());
        }
    }

    // Validadores reutilizables por las subclases.
    protected function textoValido(?string $valor, int $max): bool {
        return is_string($valor) && trim($valor) !== '' && mb_strlen(trim($valor)) <= $max;
    }

    protected function telefonoValido(?string $valor): bool {
        return is_string($valor) && preg_match('/^[0-9+\s-]{7,20}$/', trim($valor)) === 1;
    }
}

```

---

## Ruta: `backend\models\Traslado.php`

```php
<?php

require_once __DIR__ . '/../config/Database.php';

/**
 * Traslado de pacientes u objetos (módulo Ambulancias).
 * Concentra las restricciones no estructurales (RNE01 a RNE07) en la capa de negocio y
 * accede a los datos solo con consultas preparadas PDO.
 *
 * Flujo de estados: Pendiente -> En Curso -> Retornando -> Finalizado (o Cancelado en cualquier punto activo).
 * Una división del hospital crea la solicitud (Pendiente, sin recursos); el área de transporte
 * asigna vehículo, chofer y enfermero editándola, y luego hace el seguimiento del estado.
 */
class Traslado {
    private ?PDO $conexion;

    /** Errores de validación o de regla de negocio de la última operación. */
    public array $errores = [];
    /** Código HTTP sugerido para el error: 400 (datos), 404 (no existe) o 409 (conflicto). */
    public int $codigoError = 400;

    private const ESTADOS_ACTIVOS = ['Pendiente', 'En Curso', 'Retornando'];
    private const TRANSICIONES = [
        'Pendiente'  => ['En Curso', 'Cancelado'],
        'En Curso'   => ['Retornando', 'Cancelado'],
        'Retornando' => ['Finalizado', 'Cancelado'],
        'Finalizado' => [],
        'Cancelado'  => [],
    ];
    private const CAMPOS = [
        'fecha', 'hora_salida', 'hora_estimada_llegada', 'hora_efectiva_llegada', 'hora_retorno',
        'sector_origen', 'piso_origen', 'habitacion_origen',
        'sector_destino', 'piso_destino', 'habitacion_destino', 'direccion_destino',
        'prioridad', 'observaciones', 'division_solicitante', 'motivo_procedimiento',
        'responsable_recepcion', 'limite_horario',
        'id_vehiculo', 'id_chofer', 'id_enfermero', 'id_paciente', 'id_objeto'
    ];
    private const OBLIGATORIOS = [
        'fecha', 'hora_salida', 'hora_estimada_llegada', 'sector_origen', 'piso_origen',
        'sector_destino', 'piso_destino', 'direccion_destino', 'division_solicitante',
        'motivo_procedimiento', 'responsable_recepcion'
    ];
    private const HORAS = ['hora_salida', 'hora_estimada_llegada', 'hora_efectiva_llegada', 'hora_retorno', 'limite_horario'];
    // RNE07: departamentos del interior del país (destinos prohibidos). Montevideo y Canelones son zona metropolitana.
    private const INTERIOR = [
        'artigas', 'cerro largo', 'colonia', 'durazno', 'flores', 'florida', 'lavalleja', 'maldonado',
        'paysandu', 'rio negro', 'rivera', 'rocha', 'salto', 'soriano', 'tacuarembo', 'treinta y tres'
    ];

    private const SELECT_BASE = "SELECT t.*,
            p.nombre AS paciente_nombre, p.documento AS paciente_documento,
            o.nombre AS objeto_nombre, o.tipo_objeto,
            v.matricula AS vehiculo_matricula, v.tipo_vehiculo,
            c.nombre AS chofer_nombre, e.nombre AS enfermero_nombre
        FROM traslado t
        LEFT JOIN paciente p ON p.id_paciente = t.id_paciente
        LEFT JOIN objeto o ON o.id_objeto = t.id_objeto
        LEFT JOIN vehiculo v ON v.id_vehiculo = t.id_vehiculo
        LEFT JOIN chofer c ON c.id_chofer = t.id_chofer
        LEFT JOIN enfermero e ON e.id_enfermero = t.id_enfermero";

    public function __construct() {
        $db = new Database();
        $this->conexion = $db->conectar();
    }

    // ---------------------------------------------------------------- Consultas

    /**
     * Historial de traslados. Filtros: id_paciente, documento, id_vehiculo, id_chofer, matricula,
     * estado, fecha_desde, fecha_hasta. $idUsuario limita a los traslados creados por ese usuario.
     */
    public function listar(array $filtros = [], ?int $idUsuario = null): array {
        $sql = self::SELECT_BASE . " WHERE 1 = 1";
        $p = [];

        if (!empty($filtros['id_paciente']) && ctype_digit((string)$filtros['id_paciente'])) {
            $sql .= " AND t.id_paciente = :id_paciente";
            $p['id_paciente'] = (int)$filtros['id_paciente'];
        }
        if (!empty($filtros['documento'])) {
            $sql .= " AND p.documento = :documento";
            $p['documento'] = trim((string)$filtros['documento']);
        }
        if (!empty($filtros['id_vehiculo']) && ctype_digit((string)$filtros['id_vehiculo'])) {
            $sql .= " AND t.id_vehiculo = :id_vehiculo";
            $p['id_vehiculo'] = (int)$filtros['id_vehiculo'];
        }
        if (!empty($filtros['id_chofer']) && ctype_digit((string)$filtros['id_chofer'])) {
            $sql .= " AND t.id_chofer = :id_chofer";
            $p['id_chofer'] = (int)$filtros['id_chofer'];
        }
        if (!empty($filtros['matricula'])) {
            $sql .= " AND v.matricula = :matricula";
            $p['matricula'] = trim((string)$filtros['matricula']);
        }
        if (!empty($filtros['estado']) && isset(self::TRANSICIONES[$filtros['estado']])) {
            $sql .= " AND t.estado = :estado";
            $p['estado'] = $filtros['estado'];
        }
        if (!empty($filtros['fecha_desde']) && $this->fechaValida($filtros['fecha_desde'])) {
            $sql .= " AND t.fecha >= :fecha_desde";
            $p['fecha_desde'] = $filtros['fecha_desde'];
        }
        if (!empty($filtros['fecha_hasta']) && $this->fechaValida($filtros['fecha_hasta'])) {
            $sql .= " AND t.fecha <= :fecha_hasta";
            $p['fecha_hasta'] = $filtros['fecha_hasta'];
        }
        if ($idUsuario !== null) {
            $sql .= " AND t.id_usuario = :id_usuario";
            $p['id_usuario'] = $idUsuario;
        }
        $sql .= " ORDER BY t.fecha DESC, t.hora_salida DESC";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($p);
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): ?array {
        $stmt = $this->conexion->prepare(self::SELECT_BASE . " WHERE t.id_traslado = :id LIMIT 1");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    // ---------------------------------------------------------------- Escritura

    /**
     * Registra un traslado. Si $soloSolicitud es true (usuario de una división) se ignoran
     * vehículo, chofer y enfermero: los asigna después el área de transporte.
     */
    public function crear(array $datos, int $idUsuario, bool $soloSolicitud = false): ?int {
        $datos = $this->normalizar($datos);
        if ($soloSolicitud) {
            $datos['id_vehiculo'] = $datos['id_chofer'] = $datos['id_enfermero'] = null;
        }

        try {
            $this->conexion->beginTransaction();
            $this->bloquearRecursos($datos);

            if (!$this->validar($datos, null)) {
                $this->conexion->rollBack();
                return null;
            }

            $columnas = implode(', ', self::CAMPOS) . ', id_usuario';
            $marcas = implode(', ', array_map(fn($c) => ':' . $c, self::CAMPOS)) . ', :id_usuario';
            $stmt = $this->conexion->prepare("INSERT INTO traslado ($columnas) VALUES ($marcas)");
            $parametros = array_intersect_key($datos, array_flip(self::CAMPOS));
            $parametros['id_usuario'] = $idUsuario;
            $stmt->execute($parametros);

            $id = (int)$this->conexion->lastInsertId();
            $this->conexion->commit();
            return $id;
        } catch (PDOException $e) {
            return $this->falloInterno($e);
        }
    }

    /**
     * Edición completa (incluye asignar vehículo, chofer y enfermero). Solo se permite mientras
     * el traslado está Pendiente; una vez iniciado se gestiona con cambiarEstado().
     * Los horarios reales (llegada efectiva y retorno) no se editan por aquí.
     */
    public function actualizar(int $id, array $datos): bool {
        $actual = $this->obtenerPorId($id);
        if ($actual === null) {
            return $this->fallar(404, 'Traslado no encontrado.');
        }
        if ($actual['estado'] !== 'Pendiente') {
            return $this->fallar(409, "Solo se puede editar un traslado Pendiente. Estado actual: {$actual['estado']}.");
        }

        $datos = $this->normalizar($datos);
        $datos['hora_efectiva_llegada'] = $actual['hora_efectiva_llegada'];
        $datos['hora_retorno'] = $actual['hora_retorno'];

        try {
            $this->conexion->beginTransaction();
            $this->bloquearRecursos($datos);

            if (!$this->validar($datos, $id)) {
                $this->conexion->rollBack();
                return false;
            }

            $asignaciones = implode(', ', array_map(fn($c) => "$c = :$c", self::CAMPOS));
            $stmt = $this->conexion->prepare("UPDATE traslado SET $asignaciones WHERE id_traslado = :id_traslado");
            $parametros = array_intersect_key($datos, array_flip(self::CAMPOS));
            $parametros['id_traslado'] = $id;
            $stmt->execute($parametros);

            $this->conexion->commit();
            return true;
        } catch (PDOException $e) {
            $this->falloInterno($e);
            return false;
        }
    }

    /**
     * Seguimiento del estado. Valida la transición y registra los horarios reales:
     * al pasar a Retornando se guarda la llegada efectiva y al Finalizar la hora de retorno
     * (si no se informan, se usa la hora actual). Sincroniza el estado del vehículo.
     */
    public function cambiarEstado(int $id, string $nuevoEstado, ?string $horaEfectiva = null, ?string $horaRetorno = null): bool {
        $t = $this->obtenerPorId($id);
        if ($t === null) {
            return $this->fallar(404, 'Traslado no encontrado.');
        }
        if (!isset(self::TRANSICIONES[$nuevoEstado])) {
            return $this->fallar(400, 'El estado indicado no es válido.');
        }
        if (!in_array($nuevoEstado, self::TRANSICIONES[$t['estado']], true)) {
            return $this->fallar(409, "Transición no permitida: {$t['estado']} → $nuevoEstado.");
        }

        $efectiva = $t['hora_efectiva_llegada'];
        $retorno = $t['hora_retorno'];

        if ($nuevoEstado === 'En Curso' && ($t['id_vehiculo'] === null || $t['id_chofer'] === null)) {
            return $this->fallar(409, 'Antes de iniciar el traslado debe asignarse vehículo y chofer.');
        }
        if ($nuevoEstado === 'Retornando') {
            $efectiva = $this->normalizarHora($horaEfectiva) ?? date('H:i:s');
            if ($efectiva <= $t['hora_salida']) {
                return $this->fallar(400, 'La hora de llegada efectiva debe ser posterior a la hora de salida.');
            }
        }
        if ($nuevoEstado === 'Finalizado') {
            $retorno = $this->normalizarHora($horaRetorno) ?? date('H:i:s');
            if ($efectiva !== null && $retorno < $efectiva) {
                return $this->fallar(400, 'La hora de retorno no puede ser anterior a la llegada efectiva.');
            }
        }

        try {
            $this->conexion->beginTransaction();

            $sql = "UPDATE traslado SET estado = :estado, hora_efectiva_llegada = :efectiva, hora_retorno = :retorno
                    WHERE id_traslado = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute(['estado' => $nuevoEstado, 'efectiva' => $efectiva, 'retorno' => $retorno, 'id' => $id]);

            $this->sincronizarVehiculo($t['id_vehiculo'] !== null ? (int)$t['id_vehiculo'] : null, $nuevoEstado);

            $this->conexion->commit();
            return true;
        } catch (PDOException $e) {
            $this->falloInterno($e);
            return false;
        }
    }

    // Cancelación lógica (DELETE): el traslado queda en el historial con estado Cancelado.
    public function cancelar(int $id): bool {
        return $this->cambiarEstado($id, 'Cancelado');
    }

    // ---------------------------------------------------------------- Reglas de negocio

    /**
     * Aplica las restricciones no estructurales. Devuelve true si el traslado es válido;
     * si no, deja los mensajes en $errores y el código HTTP en $codigoError.
     */
    private function validar(array &$d, ?int $idActual): bool {
        $this->errores = [];
        $conflicto = false;

        foreach (self::OBLIGATORIOS as $campo) {
            if ($d[$campo] === null || $d[$campo] === '') {
                $this->errores[] = "El campo $campo es obligatorio.";
            }
        }
        if ($d['fecha'] !== null && !$this->fechaValida($d['fecha'])) {
            $this->errores[] = 'La fecha debe tener formato AAAA-MM-DD.';
        }
        foreach (self::HORAS as $campo) {
            if ($d[$campo] !== null && !$this->horaValida($d[$campo])) {
                $this->errores[] = "El campo $campo debe tener formato HH:MM.";
            }
        }
        if (!in_array($d['prioridad'], ['Alta', 'Media', 'Baja'], true)) {
            $this->errores[] = 'La prioridad debe ser Alta, Media o Baja.';
        }
        if (!empty($this->errores)) {
            $this->codigoError = 400;
            return false;
        }

        // RNE01: coherencia cronológica
        if ($d['hora_estimada_llegada'] <= $d['hora_salida']) {
            $this->errores[] = 'La hora estimada de llegada debe ser posterior a la hora de salida.';
        }

        // RNE07: destino restringido a la zona metropolitana
        $destino = $this->sinAcentos($d['direccion_destino'] . ' ' . $d['sector_destino']);
        foreach (self::INTERIOR as $depto) {
            if (preg_match('/\b' . preg_quote($depto, '/') . '\b/', $destino)) {
                $this->errores[] = 'No se permiten traslados al interior del país (zona metropolitana únicamente).';
                break;
            }
        }

        // RNE03: paciente u objeto, nunca ambos ni ninguno
        $paciente = $objeto = null;
        if (($d['id_paciente'] === null) === ($d['id_objeto'] === null)) {
            $this->errores[] = 'El traslado debe asociarse a un paciente o a un objeto, nunca a ambos.';
        } elseif ($d['id_paciente'] !== null) {
            $paciente = $this->buscar('paciente', 'id_paciente', $d['id_paciente']);
            if ($paciente === null) {
                $this->errores[] = 'El paciente indicado no existe.';
            }
        } else {
            $objeto = $this->buscar('objeto', 'id_objeto', $d['id_objeto']);
            if ($objeto === null) {
                $this->errores[] = 'El objeto indicado no existe.';
            }
        }

        // Tipo de vehículo exigido según la carga (RNE05, RNE06 y requisitos clínicos)
        $exigeAmbulancia = false;
        if ($paciente !== null) {
            $exigeAmbulancia = !$paciente['se_maneja_por_sus_medios'] || $paciente['requiere_oxigeno']
                || $paciente['requiere_camilla'] || $paciente['requiere_aislamiento'];
        }
        if ($objeto !== null) {
            $d['id_enfermero'] = null; // RNE03/RNE05: un objeto no lleva enfermero acompañante
            if (in_array($objeto['tipo_objeto'], ['organo', 'muestra_biologica'], true)) {
                $exigeAmbulancia = true;
            }
            if ($objeto['tipo_objeto'] === 'organo') {
                $d['prioridad'] = 'Alta'; // RNE05: prioridad máxima
                if ($d['limite_horario'] === null) {
                    $this->errores[] = 'El traslado de un órgano exige registrar la hora límite.';
                }
            }
        }

        // Vehículo (RNE04, RNE06)
        if ($d['id_vehiculo'] !== null) {
            $vehiculo = $this->buscar('vehiculo', 'id_vehiculo', $d['id_vehiculo']);
            if ($vehiculo === null) {
                $this->errores[] = 'El vehículo indicado no existe.';
            } else {
                if (in_array($vehiculo['estado'], ['Mantenimiento', 'Fuera de Servicio'], true)) {
                    $this->errores[] = "El vehículo {$vehiculo['matricula']} se encuentra en {$vehiculo['estado']} y no puede asignarse.";
                    $conflicto = true;
                }
                if ($exigeAmbulancia && $vehiculo['tipo_vehiculo'] !== 'Ambulancia') {
                    $this->errores[] = 'Por las condiciones clínicas o de la carga, este traslado requiere una Ambulancia.';
                    $conflicto = true;
                }
            }
        }

        // Chofer y enfermero
        if ($d['id_chofer'] !== null && $this->buscar('chofer', 'id_chofer', $d['id_chofer']) === null) {
            $this->errores[] = 'El chofer indicado no existe.';
        }
        if ($d['id_enfermero'] !== null && $this->buscar('enfermero', 'id_enfermero', $d['id_enfermero']) === null) {
            $this->errores[] = 'El enfermero indicado no existe.';
        }
        // RNE03: enfermero obligatorio para pacientes con prioridad Alta (se exige al asignar recursos)
        if ($paciente !== null && $d['prioridad'] === 'Alta' && $d['id_vehiculo'] !== null && $d['id_enfermero'] === null) {
            $this->errores[] = 'Un traslado de paciente con prioridad Alta requiere enfermero acompañante.';
        }

        // RNE02: exclusividad temporal de vehículo, chofer y enfermero
        if (empty($this->errores) || $conflicto) {
            $recursos = ['id_vehiculo' => 'vehículo', 'id_chofer' => 'chofer', 'id_enfermero' => 'enfermero'];
            foreach ($recursos as $columna => $nombre) {
                if ($d[$columna] !== null && $this->haySolapamiento($columna, (int)$d[$columna], $d, $idActual)) {
                    $this->errores[] = "El $nombre seleccionado ya está asignado a otro traslado activo en ese horario.";
                    $conflicto = true;
                }
            }
        }

        if (!empty($this->errores)) {
            $this->codigoError = $conflicto ? 409 : 400;
            return false;
        }
        return true;
    }

    // RNE02: ¿hay otro traslado activo del mismo recurso cuyo intervalo [salida, retorno/llegada estimada] se superpone?
    private function haySolapamiento(string $columna, int $valor, array $d, ?int $idActual): bool {
        if (!in_array($columna, ['id_vehiculo', 'id_chofer', 'id_enfermero'], true)) {
            return false; // lista blanca: el nombre de columna nunca viene del usuario
        }
        $sql = "SELECT COUNT(*) FROM traslado
                WHERE $columna = :valor AND fecha = :fecha
                  AND estado IN ('Pendiente', 'En Curso', 'Retornando')
                  AND id_traslado <> :id
                  AND hora_salida < :fin
                  AND COALESCE(hora_retorno, hora_estimada_llegada) > :inicio";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            'valor'  => $valor,
            'fecha'  => $d['fecha'],
            'id'     => $idActual ?? 0,
            'fin'    => $d['hora_retorno'] ?? $d['hora_estimada_llegada'],
            'inicio' => $d['hora_salida'],
        ]);
        return (int)$stmt->fetchColumn() > 0;
    }

    // Bloquea las filas de vehículo y chofer para que dos solicitudes simultáneas no se asignen el mismo recurso.
    private function bloquearRecursos(array $d): void {
        foreach (['vehiculo' => 'id_vehiculo', 'chofer' => 'id_chofer', 'enfermero' => 'id_enfermero'] as $tabla => $columna) {
            if ($d[$columna] !== null) {
                $stmt = $this->conexion->prepare("SELECT $columna FROM $tabla WHERE $columna = :id FOR UPDATE");
                $stmt->execute(['id' => $d[$columna]]);
            }
        }
    }

    /**
     * El vehículo pasa a 'En Traslado' al iniciar y vuelve a 'Disponible' al finalizar o cancelar,
     * salvo que esté en Mantenimiento/Fuera de Servicio o tenga otro traslado en curso.
     */
    private function sincronizarVehiculo(?int $idVehiculo, string $nuevoEstado): void {
        if ($idVehiculo === null) {
            return;
        }
        if ($nuevoEstado === 'En Curso') {
            $sql = "UPDATE vehiculo SET estado = 'En Traslado' WHERE id_vehiculo = :id AND estado = 'Disponible'";
        } elseif ($nuevoEstado === 'Finalizado' || $nuevoEstado === 'Cancelado') {
            $sql = "UPDATE vehiculo SET estado = 'Disponible'
                    WHERE id_vehiculo = :id AND estado = 'En Traslado'
                      AND NOT EXISTS (SELECT 1 FROM traslado WHERE id_vehiculo = :id2 AND estado IN ('En Curso', 'Retornando'))";
        } else {
            return;
        }
        $stmt = $this->conexion->prepare($sql);
        $parametros = ['id' => $idVehiculo];
        if (str_contains($sql, ':id2')) {
            $parametros['id2'] = $idVehiculo;
        }
        $stmt->execute($parametros);
    }

    // ---------------------------------------------------------------- Auxiliares

    // Lista blanca de campos; '' -> null; ids a int; horas HH:MM -> HH:MM:SS.
    private function normalizar(array $datos): array {
        $limpio = [];
        foreach (self::CAMPOS as $campo) {
            $valor = $datos[$campo] ?? null;
            if (is_string($valor)) {
                $valor = trim($valor);
                $valor = ($valor === '') ? null : $valor;
            }
            if (str_starts_with($campo, 'id_') && $valor !== null) {
                $valor = ctype_digit((string)$valor) ? (int)$valor : 0;
            }
            if (in_array($campo, self::HORAS, true)) {
                $valor = $this->normalizarHora($valor);
            }
            $limpio[$campo] = $valor;
        }
        $limpio['prioridad'] = $limpio['prioridad'] ?? 'Media';
        return $limpio;
    }

    private function normalizarHora(?string $hora): ?string {
        if ($hora === null || $hora === '') {
            return null;
        }
        return preg_match('/^\d{2}:\d{2}$/', $hora) ? $hora . ':00' : $hora;
    }

    private function horaValida(string $hora): bool {
        return preg_match('/^([01]\d|2[0-3]):[0-5]\d:[0-5]\d$/', $hora) === 1;
    }

    private function fechaValida($fecha): bool {
        if (!is_string($fecha)) {
            return false;
        }
        $obj = DateTime::createFromFormat('Y-m-d', $fecha);
        return $obj && $obj->format('Y-m-d') === $fecha;
    }

    private function sinAcentos(string $texto): string {
        return strtr(mb_strtolower($texto, 'UTF-8'), ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n']);
    }

    // Búsqueda por clave primaria en tablas de referencia (los nombres de tabla/columna son constantes del código).
    private function buscar(string $tabla, string $clave, int $id): ?array {
        $stmt = $this->conexion->prepare("SELECT * FROM $tabla WHERE $clave = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    private function fallar(int $codigo, string $mensaje): bool {
        $this->codigoError = $codigo;
        $this->errores = [$mensaje];
        return false;
    }

    private function falloInterno(PDOException $e): ?int {
        if ($this->conexion->inTransaction()) {
            $this->conexion->rollBack();
        }
        error_log('Error en Traslado: ' . $e->getMessage());
        $this->codigoError = 500;
        $this->errores = ['Error en el servidor al procesar el traslado.'];
        return null;
    }
}

```

---

## Ruta: `backend\models\Usuario.php`

```php
<?php
require_once __DIR__ . '/../config/Database.php';

class Usuario {
    private ?PDO $conexion;
    private string $tabla = 'usuario';

    public ?int $id_usuario = null;
    public ?string $email = null;
    public ?string $nombre = null;
    public ?string $contrasenia = null;
    public ?string $rol = null;
    public ?string $estado = null;

    public function __construct() {
        $db = new Database();
        $this->conexion = $db->conectar();
    }

     //Busca un usuario por su dirección de correo electrónico institucional
    public function obtenerPorEmail(string $email): ?array {
        $sql = "SELECT id_usuario, email, nombre, contrasenia, rol, estado 
 FROM {$this->tabla} 
 WHERE email = :email 
 LIMIT 1";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        $usuario = $stmt->fetch();
        return $usuario ?: null;
    }

    
     //Busca un usuario por su ID de clave primaria
    public function obtenerPorId(int $id): ?array {
        $sql = "SELECT id_usuario, email, nombre, contrasenia, rol, estado 
 FROM {$this->tabla} 
 WHERE id_usuario = :id 
 LIMIT 1";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $usuario = $stmt->fetch();
        return $usuario ?: null;
    }

    
     //Valida las credenciales recibidas contra el hash de la base de datos y el estado activo
    public function autenticar(string $email, string $password): array {
        $usuario = $this->obtenerPorEmail($email);

        if (!$usuario) {
            return [
                'exito'   => false,
                'codigo'  => 401,
                'mensaje' => 'Credenciales inválidas.'
            ];
        }

        // Validación de usuario activo
        if ($usuario['estado'] !== 'Activo') {
            return [
                'exito'   => false,
                'codigo'  => 403,
                'mensaje' => 'Cuenta deshabilitada. Comuníquese con el Administrador IT.'
            ];
        }

        // Verificación criptografica con password_verify
        if (!password_verify($password, $usuario['contrasenia'])) {
            return [
                'exito'   => false,
                'codigo'  => 401,
                'mensaje' => 'Credenciales inválidas.'
            ];
        }

        // Asignación interna
        $this->id_usuario = $usuario['id_usuario'];
        $this->email      = $usuario['email'];
        $this->nombre     = $usuario['nombre'];
        $this->rol        = $usuario['rol'];
        $this->estado     = $usuario['estado'];

        return [
            'exito'   => true,
            'codigo'  => 200,
            'mensaje' => 'Autenticación exitosa.',
            'usuario' => [
                'id_usuario' => $usuario['id_usuario'],
                'email'      => $usuario['email'],
                'nombre'     => $usuario['nombre'],
                'rol'        => $usuario['rol']
            ]
        ];
    }

     //Obtiene el listado completo de todos los usuarios de la institución
    public function listarTodos(): array {
        $sql = "SELECT id_usuario, email, nombre, rol, estado FROM {$this->tabla} ORDER BY nombre ASC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    
     // Registra de manera segura un nuevo funcionario en la institución
    public function crear(array $datos): bool {
        // Encriptación criptográfica (Secure Hash)
        $hashPassword = password_hash($datos['contrasenia'], PASSWORD_BCRYPT);

        $sql = "INSERT INTO {$this->tabla} (email, nombre, contrasenia, rol, estado) 
                VALUES (:email, :nombre, :contrasenia, :rol, :estado)";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':email', $datos['email'], PDO::PARAM_STR);
        $stmt->bindParam(':nombre', $datos['nombre'], PDO::PARAM_STR);
        $stmt->bindParam(':contrasenia', $hashPassword, PDO::PARAM_STR);
        $stmt->bindParam(':rol', $datos['rol'], PDO::PARAM_STR);
        $stmt->bindParam(':estado', $datos['estado'], PDO::PARAM_STR);

        return $stmt->execute();
    }

     //Actualiza de manera segura la información de un funcionario administrativo existente
    public function actualizar(int $id, array $datos): bool {
        $actualizarContrasenia = !empty($datos['contrasenia']);

        $sql = "UPDATE {$this->tabla} 
                SET email = :email, 
                    nombre = :nombre, 
                    rol = :rol, 
                    estado = :estado";

        if ($actualizarContrasenia) {
            $sql .= ", contrasenia = :contrasenia";
        }

        $sql .= " WHERE id_usuario = :id";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':email', $datos['email'], PDO::PARAM_STR);
        $stmt->bindParam(':nombre', $datos['nombre'], PDO::PARAM_STR);
        $stmt->bindParam(':rol', $datos['rol'], PDO::PARAM_STR);
        $stmt->bindParam(':estado', $datos['estado'], PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($actualizarContrasenia) {
            $hashPassword = password_hash($datos['contrasenia'], PASSWORD_BCRYPT);
            $stmt->bindParam(':contrasenia', $hashPassword, PDO::PARAM_STR);
        }

        return $stmt->execute();
    }

     //Elimina la ficha de un usuario administrativo por su ID de clave primaria.
    public function eliminar(int $id): bool {
        $sql = "DELETE FROM {$this->tabla} WHERE id_usuario = :id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}

```

---

## Ruta: `backend\models\Vehiculo.php`

```php
<?php

require_once __DIR__ . '/Recurso.php';

// Vehículo de la flota del hospital (Ambulancia o Auto).
class Vehiculo extends Recurso {
    protected string $tabla = 'vehiculo';
    protected string $clavePrimaria = 'id_vehiculo';
    protected array $campos = ['tipo_vehiculo', 'matricula', 'estado'];

    public function validar(array $datos): array {
        $errores = [];
        if (!in_array($datos['tipo_vehiculo'] ?? null, ['Ambulancia', 'Auto'], true)) {
            $errores[] = "El tipo de vehículo debe ser 'Ambulancia' o 'Auto'.";
        }
        if (!$this->textoValido($datos['matricula'] ?? null, 20)) {
            $errores[] = 'La matrícula es obligatoria (máx. 20 caracteres).';
        }
        if (!in_array($datos['estado'] ?? null, ['Disponible', 'En Traslado', 'Mantenimiento', 'Fuera de Servicio'], true)) {
            $errores[] = 'El estado del vehículo no es válido.';
        }
        return $errores;
    }

    // Permite filtrar la flota por ?estado= y ?tipo_vehiculo=.
    public function listar(array $filtros = []): array {
        $sql = "SELECT * FROM vehiculo WHERE 1 = 1";
        $parametros = [];
        if (!empty($filtros['estado'])) {
            $sql .= " AND estado = :estado";
            $parametros['estado'] = $filtros['estado'];
        }
        if (!empty($filtros['tipo_vehiculo'])) {
            $sql .= " AND tipo_vehiculo = :tipo_vehiculo";
            $parametros['tipo_vehiculo'] = $filtros['tipo_vehiculo'];
        }
        $sql .= " ORDER BY id_vehiculo ASC";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll();
    }
}

```

---

## Ruta: `frontend\index.html`

```
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S.I.G.S.M. - Acceso Unificado</title>
    <link rel="stylesheet" href="css/style-login.css">
</head>
<body>
    <main class="login-container">
        <section class="login-card">
            <header class="login-header">
                <div class="login-logo-container">
                    <img src="img/HospitalDeClinicas.png" alt="Hospital de Clínicas" class="login-logo">
                </div>
                <p>Gestión de Servicios Médicos y Traslados</p>
            </header>

            <div id="alerta" class="alerta oculta" role="alert"></div>

            <!-- Formulario de acceso para los funcionarios -->
            <form id="loginForm" novalidate>
                <div class="form-group">
                    <label for="email">Correo Institucional</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="usuario@hc.edu.uy" 
                        required 
                        autocomplete="username"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="••••••••" 
                        required 
                        autocomplete="current-password"
                    >
                </div>

                <button type="submit" id="btnIngresar" class="btn-submit">
                    Ingresar como Funcionario
                </button>
            </form>

            <!-- Acceso directo para Pacientes/Invitados -->
            <div class="patient-access-box">
                <p>¿Es paciente del Hospital de Clínicas o desea ver documentación informativa?</p>
                <a href="html/portal-invitados.html" class="btn-patient">
                    Ingresar como Paciente / Invitado
                </a>
            </div>

            <footer class="login-footer">
                <p>¿Necesita ayuda para ingresar? Contáctenos al correo <a href="mailto:contacto@hc.edu.uy" style="color: #006699; text-decoration: underline;">contacto@hc.edu.uy</a></p>
                <p style="margin-top: 0.75rem; font-size: 0.65rem; color: #94a3b8;">Copyright &copy; 2026 Academia Sigma. Todos los derechos reservados.</p>
            </footer>
        </section>
    </main>

    <script src="js/login.js"></script>
</body>
</html>

```

---

## Ruta: `frontend\css\style-ambulancias.css`

```
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap');

/* cards estaticas SECTION */
.stats-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
    margin-bottom: 2rem;
}
@media (min-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(4, 1fr) !important;
        gap: 1rem !important;
    }
}

.stat-card.stat-total { border-left-color: #006699; }
.stat-card.stat-pending { border-left-color: #f59e0b; }
.stat-card.stat-progress { border-left-color: #2563eb; }
.stat-card.stat-completed { border-left-color: #10b981; }

.stat-icon {
    font-size: 2.25rem;
}

.stat-desc h3 {
    font-size: 1.8rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
    line-height: 1.1;
}

.stat-desc p {
    font-size: 0.85rem;
    color: #64748b;
    margin: 0.15rem 0 0 0;
    text-transform: uppercase;
    font-weight: 600;
}

/* Grid del workspace dual */
.ambulancias-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
}

/*Grid responsivo mediante container queries en anchos inferiores */
.form-card, .list-card {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
}

.form-subtitle {
    font-size: 0.85rem;
    color: #64748b;
    margin-top: 0.75rem !important;
    margin-bottom: 1.5rem;
}

.form-row-2 {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
}

@media (min-width: 480px) {
    .form-row-2 {
        grid-template-columns: 1fr 1fr;
    }
}

.styled-form select, .styled-form input:not([type="checkbox"]):not([type="radio"]) {
    width: 100%;
    padding: 0.55rem;
    border: 1px solid #cbd5e1;
    border-radius: 4px;
    font-size: 0.9rem;
    color: #1e293b;
    background-color: #f8fafc;
}

.styled-form select:focus, .styled-form input:not([type="checkbox"]):not([type="radio"]):focus {
    border-color: #006699;
    background-color: white;
    outline: none;
}

/* Grilla 2x2 de condiciones clínicas del paciente */
.condiciones-grid-2x2 {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.5rem;
    margin-top: 0.35rem;
}

.condicion-chip {
    display: flex !important;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.65rem;
    background: #f8fafc;
    border: 1.5px solid #cbd5e1;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.8rem !important;
    font-weight: 600 !important;
    color: #334155 !important;
    margin-bottom: 0 !important;
    transition: all 0.15s ease;
    user-select: none;
}

.condicion-chip:hover {
    border-color: #0284c7;
    background: #f0f9ff;
}

.condicion-chip input[type="checkbox"] {
    width: 16px !important;
    height: 16px !important;
    margin: 0 !important;
    accent-color: #006699;
    cursor: pointer;
    flex-shrink: 0;
}

.condicion-chip:has(input:checked) {
    background: #e0f2fe;
    border-color: #0284c7;
    color: #0c4a6e !important;
}

.list-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: 1rem;
    margin-bottom: 1rem;
    gap: 1rem;
    flex-wrap: wrap;
}

.btn-small {
    padding: 0.35rem 0.65rem !important;
    font-size: 0.75rem !important;
}

.btn-action-highlight {
    background-color: #0284c7;
    color: white;
    text-decoration: none;
    font-weight: 600;
    padding: 0.45rem 0.85rem;
    border-radius: 4px;
    font-size: 0.8rem;
    border: none;
    cursor: pointer;
}

.btn-action-highlight:hover {
    background-color: #0369a1;
}

/*Badges de estado*/

.status-badge {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    font-weight: 700;
    border-radius: 2px;
    display: inline-block;
}

.status-badge.status-yellow {
    background-color: #fef3c7;
    color: #d97706;
}

.status-badge.status-blue {
    background-color: #dbeafe;
    color: #1d4ed8;
}

.status-badge.status-green {
    background-color: #d1fae5;
    color: #047857;
}

.status-badge.status-red {
    background-color: #fee2e2;
    color: #b91c1c;
}

.pulse-badge {
    position: relative;
}

.badge-prio-alta {
     background-color: #fee2e2;
     color: #b91c1c; 
     padding: 0.2rem 0.4rem;
     border-radius: 2px; 
     font-weight: bold;
      font-size: 0.75rem; }

.badge-prio-media {
     background-color: #ffedd5; 
    color: #d97706; 
    padding: 0.2rem 0.4rem; 
    border-radius: 2px; 
    font-weight: bold; 
    font-size: 0.75rem; }

.badge-prio-baja { 
    background-color: #f1f5f9;
     color: #475569; 
     padding: 0.2rem 0.4rem; 
     border-radius: 2px; 
     font-weight: bold; 
     font-size: 0.75rem; }

/* Alerts del parpadeo animado */
.alert-badge-table {
    background-color: #ffedd5;
    color: #c2410c;
    border: 1px solid #fed7aa;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: bold;
    display: inline-block;
}

.blink-alert {
    animation: alert-blink 0.5s infinite alternate;
}

@keyframes alert-blink {
    0% {
        box-shadow: 0 0 0 rgba(239, 68, 68, 0);
        background-color: #ffedd5;
    }
    100% {
        box-shadow: 0 0 8px rgba(239, 68, 68, 0.4);
        background-color: #fecaca;
        color: #b91c1c;
    }
}

/* Estilo del marco de simulación de celular */
.mobile-body {
    background-color: #0f172a;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    padding: 1rem 0;
}

.mobile-frame {
    width: 100%;
    max-width: 410px;
    height: 100%;
    min-height: 740px;
    background: #f8fafc;
    border-radius: 30px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    border: 10px solid #1e293b;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.mobile-header {
    background: #006699;
    color: white;
    text-align: center;
    padding: 1.5rem 1rem;
    border-bottom: 3px solid #0284c7;
}

.mobile-logo {
    height: 65px !important;
    max-height: 65px !important;
    width: auto;
    object-fit: contain;
    margin-bottom: 0.5rem;
}

.mobile-header h2 {
    font-size: 1.25rem;
    font-weight: 700;
    margin: 0;
}

.mobile-header p {
    font-size: 0.75rem;
    opacity: 0.9;
    margin: 0.15rem 0 0 0;
}

.mobile-container {
    flex: 1;
    padding: 1rem;
    overflow-y: auto;
}

.select-driver-card {
    padding: 0.85rem;
    margin-bottom: 1rem;
    border: 1px dashed #0284c7;
    background: #f0f9ff;
}

.select-driver-card select {
    width: 100%;
    padding: 0.45rem;
    border: 1px solid #cbd5e1;
    border-radius: 4px;
    font-size: 0.85rem;
    margin-top: 0.25rem;
}

.driver-status {
    font-size: 0.7rem;
    font-weight: bold;
    color: #166534;
    margin: 0.35rem 0 0 0;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.pulse-green {
    font-size: 0.8rem;
    color: #10b981;
    animation: alert-blink 1s infinite alternate;
}

.no-travel-spinner {
    width: 32px;
    height: 32px;
    border: 4px solid #cbd5e1;
    border-top: 4px solid #006699;
    border-radius: 50%;
    margin: 1.5rem auto 0 auto;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Travel Details */
.travel-details-card {
    background: white;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.travel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #f1f5f9;
    padding-bottom: 0.75rem;
    margin-bottom: 1rem;
}

.travel-header h4 {
    font-size: 1rem;
    color: #0f172a;
    margin: 0;
}

.badge-prioridad {
    padding: 0.15rem 0.45rem;
    border-radius: 4px;
    font-size: 0.65rem;
    font-weight: 800;
}

/* Historial de ruta */
.travel-route {
    display: flex;
    flex-direction: column;
    position: relative;
    padding-left: 0.5rem;
    margin-bottom: 1rem;
}

.route-point {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}

.route-icon {
    font-size: 1.1rem;
    z-index: 2;
}

.route-line {
    width: 2px;
    height: 25px;
    background-color: #cbd5e1;
    position: absolute;
    left: 13px;
    top: 18px;
    z-index: 1;
}

.route-desc small {
    display: block;
    font-size: 0.65rem;
    color: #64748b;
    font-weight: 700;
}

.route-desc strong {
    font-size: 0.85rem;
    color: #0f172a;
}

.travel-meta {
    background: #f8fafc;
    border-radius: 8px;
    padding: 0.75rem;
    font-size: 0.8rem;
    margin-bottom: 1.25rem;
    border: 1px solid #f1f5f9;
}

.travel-meta p {
    margin: 0.35rem 0;
    color: #334155;
}

.badge-estado {
    padding: 0.1rem 0.35rem;
    font-size: 0.7rem;
    font-weight: 700;
    border-radius: 4px;
}

.btn-mobile {
    width: 100%;
    padding: 0.85rem;
    font-size: 0.95rem;
    font-weight: bold;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.btn-mobile-primary { background: #006699; }
.btn-mobile-primary:hover { background: #004d80; }
.btn-mobile-success { background: #10b981; }
.btn-mobile-success:hover { background: #059669; }

/* Botones rápidos para alertas */
.alert-trigger-box {
    margin-top: 1.25rem;
    border-top: 1px solid #f1f5f9;
    padding-top: 0.75rem;
}

.alert-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.btn-mobile-alert {
    flex: 1;
    min-width: 100px;
    padding: 0.45rem;
    background-color: #fff7ed;
    color: #c2410c;
    border: 1px solid #ffedd5;
    border-radius: 4px;
    font-size: 0.72rem;
    font-weight: bold;
    cursor: pointer;
}

.btn-mobile-alert:hover {
    background-color: #ffedd5;
}

.mobile-footer {
    background: #1e293b;
    padding: 0.85rem;
}

.mobile-footer p {
    font-size: 0.62rem;
    color: #94a3b8;
    margin: 0;
}


/* Optimizaciones de pantalla para pc*/
@media (min-width: 768px) {
    .container {
        max-width: 95% !important;
        width: 95% !important;
        padding: 0 1.5rem !important;
    }
    .ambulancias-grid {
        grid-template-columns: 350px 1fr !important;
        gap: 1.5rem !important;
    }
    .data-table th, .data-table td {
        padding: 0.75rem 0.85rem !important;
        font-size: 0.85rem !important;
    }
}
@media (min-width: 1200px) {
    .container {
        max-width: 1450px !important;
        width: 95% !important;
    }
    .stats-grid {
        gap: 1.5rem !important;
    }
    .ambulancias-grid {
        grid-template-columns: 1fr 380px !important;
        gap: 2rem !important;
    }
    .data-table th, .data-table td {
        padding: 0.95rem 1.15rem !important;
        font-size: 0.9rem !important;
    }
}

@media (min-width: 768px) and (max-width: 1023px) {
    .stats-grid {
        grid-template-columns: repeat(4, 1fr) !important;
        gap: 1rem !important;
    }
    .ambulancias-grid {
        grid-template-columns: 1fr !important;
    }
}

.oculta {
    display: none !important;
}

/* override de las dimensiones del logo de S.I.G.S.M. */

.header-logo, .portal-logo, .login-logo {
    max-height: 55px !important;
    height: 50px !important;
    width: auto !important;
    object-fit: contain !important;
}
.navbar {
    background-color: #006699;
    color: #ffffff;
    padding: 0.5rem 2rem !important;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    min-height: 75px !important;
}

/* Rediseño de las cards de estadísticas */
.stat-icon-container {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #ffffff;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    flex-shrink: 0;
}
.stat-svg-icon {
    width: 24px;
    height: 24px;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 1.25rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    border: 1.5px solid #cbd5e1;
    transition: transform 0.22s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.22s cubic-bezier(0.4, 0, 0.2, 1);
}
.stat-card:hover { transform: none !important; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05) !important; border-color: inherit; }

.stat-card.stat-total {
    border-color: #bae6fd;
    background-color: #ffffff !important;
}
.stat-card.stat-total .stat-icon-container {
    color: #006699;
    background-color: #ffffff;
    border: 1.5px solid #bae6fd;
}

.stat-card.stat-pending {
    border-color: #fef3c7;
    background-color: #ffffff !important;
}
.stat-card.stat-pending .stat-icon-container {
    color: #f59e0b;
    background-color: #ffffff;
    border: 1.5px solid #fef3c7;
}

.stat-card.stat-progress {
    border-color: #bfdbfe;
    background-color: #ffffff !important;
}
.stat-card.stat-progress .stat-icon-container {
    color: #2563eb;
    background-color: #ffffff;
    border: 1.5px solid #bfdbfe;
}

.stat-card.stat-completed {
    border-color: #bbf7d0;
    background-color: #ffffff !important;
}
.stat-card.stat-completed .stat-icon-container {
    color: #10b981;
    background-color: #ffffff;
    border: 1.5px solid #bbf7d0;
}

```

---

## Ruta: `frontend\css\style-documentos.css`

```
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap');

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

body {
    background-color: #0b418521;
    color: #1e293b;
}

.navbar {
    background-color: #006699;
    color: #ffffff;
    padding: 0.5rem 2rem !important;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    min-height: 75px !important;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.btn-nav {
    color: #ffffff;
    text-decoration: none;
    background-color: rgba(255, 255, 255, 0.15);
    padding: 0.45rem 0.8rem;
    border-radius: 4px;
    font-size: 0.875rem;
    transition: background 0.2s;
}

.btn-nav:hover {
    background-color: rgba(255, 255, 255, 0.3);
}

.btn-logout {
    background-color: #e11d48;
    color: #ffffff;
    border: none;
    padding: 0.45rem 0.8rem;
    border-radius: 4px;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
}

.btn-logout:hover {
    background-color: #be123c;
}

.container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.toolbar {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

@media (min-width: 768px) {
    .toolbar {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
    }
}

.search-filters {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    width: 100%;
}

@media (min-width: 768px) {
    .search-filters {
        flex-direction: row;
        max-width: 600px;
    }
}

.search-filters input,
.search-filters select {
    padding: 0.65rem 0.9rem;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 0.95rem;
    outline: none;
    background: #ffffff;
    width: 100%;
}

.search-filters input:focus,
.search-filters select:focus {
    border-color: #006699;
    box-shadow: 0 0 0 3px rgba(0, 102, 153, 0.15);
}

.toolbar-actions {
    display: flex;
    gap: 0.75rem;
}

.btn-primary {
    background-color: #006699;
    color: #ffffff;
    border: none;
    padding: 0.65rem 1.2rem;
    border-radius: 6px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
}

.btn-primary:hover {
    background-color: #004d73;
}

.btn-secondary {
    background-color: #0284c7;
    color: #ffffff;
    border: none;
    padding: 0.65rem 1.2rem;
    border-radius: 6px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
}

.btn-secondary:hover {
    background-color: #0369a1;
}

.alerta {
    padding: 0.75rem 1rem;
    border-radius: 6px;
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.alerta.oculta { display: none; }
.alerta.error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
.alerta.exito { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }

.table-card {
    background: #ffffff;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.table-responsive {
    width: 100%;
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
    font-size: 0.9rem;
}

.data-table th, 
.data-table td {
    padding: 0.9rem 1rem;
    border-bottom: 1px solid #f1f5f9;
}

.data-table th {
    background-color: #f8fafc;
    color: #475569;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
}

.data-table tr:hover {
    background-color: #f8fafc;
}

.badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-publico { background: #e0f2fe; color: #0369a1; }
.badge-sensible { background: #fee2e2; color: #b91c1c; }

.btn-action {
    background: none;
    border: 1px solid #cbd5e1;
    padding: 0.35rem 0.65rem;
    border-radius: 4px;
    font-size: 0.8rem;
    cursor: pointer;
    margin-right: 0.25rem;
    text-decoration: none;
    color: #334155;
    display: inline-block;
}

.btn-action:hover { background-color: #e2e8f0; }
.btn-action.btn-delete { color: #e11d48; border-color: #fecaca; }
.btn-action.btn-delete:hover { background-color: #ffe4e6; }

.text-center { text-align: center; }

/* Modales */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(15, 23, 42, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    z-index: 1000;
}

.modal.oculta { display: none !important; }

.modal-content {
    background: #ffffff;
    width: 100%;
    max-width: 520px;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.modal-header {
    padding: 1rem 2rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.btn-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #64748b;
    cursor: pointer;
}

form {
    padding: 1.5rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    color: #475569;
    margin-bottom: 0.3rem;
}

.form-group input, 
.form-group select {
    width: 100%;
    padding: 0.6rem 0.8rem;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 0.9rem;
    outline: none;
}

.form-group-checkbox {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1.25rem;
}

.form-group-checkbox label {
    font-size: 0.85rem;
    color: #475569;
    cursor: pointer;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    margin-top: 1rem;
}

.btn-secondary-cancel {
    background: #e2e8f0;
    color: #334155;
    border: none;
    padding: 0.65rem 1.2rem;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
}

.btn-secondary-cancel:hover { background: #cbd5e1; }

.modal-qr-content {
    max-width: 380px;
    text-align: center;
}

.qr-body {
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
}

/* Logo */
.header-logo {
    height: 115px !important;
    max-width: 100%;
    object-fit: contain;
    display: block;
}

/* Ajustes de reposive para los logos PNG */
.header-logo, .portal-logo, .login-logo {
    max-height: 55px !important;
    height: 50px !important;
    width: auto !important;
    object-fit: contain !important;
}

/* Footer layout */
body {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}
.app-footer {
    text-align: center;
    padding: 1.5rem;
    margin-top: auto; 
    border-top: 1px solid #e2e8f0;
    color: #64748b;
    font-size: 0.8rem;
    background-color: #ffffff;
    width: 100%;
    box-sizing: border-box;
}

.header-logo, .portal-logo, .login-logo {
    max-height: 55px !important;
    height: 50px !important;
    width: auto !important;
    object-fit: contain !important;
}
.navbar {
    background-color: #006699;
    color: #ffffff;
    padding: 0.5rem 2rem !important;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    min-height: 75px !important;
}

```

---

## Ruta: `frontend\css\style-login.css`

```
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap');

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

body {
    background: #031c2b;
    color: #334155;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    padding: 1rem;
}

.login-container {
    width: 100%;
    max-width: 440px;
}

.login-card {
    background: #ffffff;
    padding: 2.5rem 2rem;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06);
    border-top: 5px solid #006699;
}

.login-header {
    text-align: center;
    margin-bottom: 1.5rem;
}

.badge-hospital {
    display: inline-block;
    background: #e0f2fe;
    color: #0369a1;
    font-size: 0.8rem;
    font-weight: 700;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    margin-bottom: 0.6rem;
    text-transform: uppercase;
}

.login-header h1 {
    color: #0f172a;
    font-size: 1.8rem;
    letter-spacing: -0.5px;
}

.login-header p {
    color: #64748b;
    font-size: 0.9rem;
    margin-top: 0.25rem;
}

.alerta {
    padding: 0.75rem 1rem;
    border-radius: 6px;
    font-size: 0.875rem;
    margin-bottom: 1.25rem;
    display: block;
    text-align: center;
}

.alerta.oculta {
    display: none;
}

.alerta.error {
    background-color: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

.alerta.exito {
    background-color: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.form-group {
    margin-bottom: 1.25rem;
}

.form-group label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: #475569;
    margin-bottom: 0.4rem;
}

.form-group input {
    width: 100%;
    padding: 0.75rem 0.9rem;
    border: 1.5px solid #cbd5e1;
    border-radius: 6px;
    font-size: 0.95rem;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.form-group input:focus {
    border-color: #006699;
    box-shadow: 0 0 0 3px rgba(0, 102, 153, 0.15);
}

.btn-submit {
    width: 100%;
    background-color: #006699;
    color: #ffffff;
    border: none;
    padding: 0.85rem;
    border-radius: 6px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.2s;
}

.btn-submit:hover {
    background-color: #004d73;
}

.btn-submit:disabled {
    background-color: #94a3b8;
    cursor: not-allowed;
}

.patient-access-box {
    margin-top: 1.75rem;
    border-top: 1px dashed #cbd5e1;
    padding-top: 1.5rem;
    text-align: center;
}

.patient-access-box p {
    font-size: 0.85rem;
    color: #64748b;
    margin-bottom: 0.85rem;
    line-height: 1.4;
}

.btn-patient {
    display: block;
    width: 100%;
    background-color: #0284c7;
    color: #ffffff;
    text-decoration: none;
    text-align: center;
    padding: 0.8rem;
    border-radius: 6px;
    font-size: 0.95rem;
    font-weight: 600;
    transition: background-color 0.2s;
}

.btn-patient:hover {
    background-color: #0369a1;
}

.login-footer {
    margin-top: 1.75rem;
    text-align: center;
    border-top: 1px solid #f1f5f9;
    padding-top: 1rem;
}

.login-footer p {
    font-size: 0.72rem;
    color: #94a3b8;
}


.login-logo-container {
    text-align: center;
    margin-bottom: 1.5rem;
    padding: 0.5rem;
}
.login-logo {
    height: 160px;
    max-width: 100%;
    object-fit: contain;
}

.header-logo, .portal-logo, .login-logo {
    max-height: 120px !important;
    height: 120px !important;
    width: auto !important;
    object-fit: contain !important;
}

.header-logo, .portal-logo, .login-logo {
    max-height: 120px !important;
    height: 120px !important;
    width: auto !important;
    object-fit: contain !important;
}
.navbar {
    min-height: 135px !important;
}

```

---

## Ruta: `frontend\css\style-panel.css`

```
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap');

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

body {
    background: #0b418521;
    color: #1e293b;
}

.navbar {
    background: #006699;
    color: white;
    padding: 0.5rem 2rem !important;
    display: flex;
    justify-content: space-between;
    align-items: center;
    min-height: 75px !important;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.badge-rol {
    background: rgba(255, 255, 255, 0.2);
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    text-transform: uppercase;
}

.btn-logout {
    background: #e11d48;
    border: none;
    color: white;
    padding: 0.5rem 0.85rem;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 600;
}

.btn-logout:hover {
    background: #be123c;
}

.container {
    max-width: 1000px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.welcome-box h1 {
    font-size: 1.85rem;
    margin-bottom: 0.5rem;
}

.welcome-box p {
    color: #64748b;
    margin-bottom: 2rem;
}

.cards-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
}
@media (min-width: 768px) {
    .cards-grid {
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)) !important;
    }
}

.card {
    background: white;
    padding: 1.75rem;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.04), 0 2px 4px -1px rgba(0, 0, 0, 0.02);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    transition: transform 0.22s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.22s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.22s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
}
.card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 24px -4px rgba(0, 102, 153, 0.12), 0 6px 12px -2px rgba(0, 102, 153, 0.06);
    border-color: #006699;
}
.card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 24px -4px rgba(0, 102, 153, 0.12), 0 6px 12px -2px rgba(0, 102, 153, 0.06);
    border-color: #006699;
}

.card h3 {
    color: #006699;
    margin-bottom: 0.75rem;
}

.card p {
    color: #475569;
    font-size: 0.95rem;
    margin-bottom: 1.5rem;
    flex-grow: 1;
}

.btn-card-action {
    display: inline-block;
    color: #006699;
    font-weight: 600;
    text-decoration: none;
    font-size: 0.95rem;
}

.btn-card-action:hover {
    text-decoration: underline;
}

.card-locked {
    background-color: #f1f5f9;
    border-color: #cbd5e1;
    opacity: 0.85;
}

.card-locked h3 {
    color: #64748b;
}

.badge-locked {
    display: inline-block;
    background: #e2e8f0;
    color: #475569;
    font-size: 0.8rem;
    font-weight: 600;
    padding: 0.35rem;
    border-radius: 4px;
    text-align: center;
}

.header-logo {
    height: 115px !important;
    max-width: 100%;
    object-fit: contain;
    display: block;
}

.header-logo, .portal-logo, .login-logo {
    max-height: 55px !important;
    height: 50px !important;
    width: auto !important;
    object-fit: contain !important;
}

/* Footer layout */
body {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}
.app-footer {
    text-align: center;
    padding: 1.5rem;
    margin-top: auto; 
    border-top: 1px solid #e2e8f0;
    color: #64748b;
    font-size: 0.8rem;
    background-color: #ffffff;
    width: 100%;
    box-sizing: border-box;
}

.welcome-box {
    text-align: center;
    margin-bottom: 2.5rem;
}

.btn-card-action::after {
    content: "";
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    z-index: 10;
}
.btn-card-action {
    display: inline-block;
    color: #006699;
    font-weight: 600;
    text-decoration: none;
    font-size: 0.95rem;
    margin-top: 1rem;
    transition: color 0.2s;
}
.card:hover .btn-card-action {
    color: #0284c7;
    text-decoration: underline;
}

.welcome-box {
    text-align: center;
    margin-bottom: 2.5rem;
}

.btn-card-action::after {
    content: "";
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    z-index: 10;
}
.btn-card-action {
    display: inline-block;
    color: #006699;
    font-weight: 600;
    text-decoration: none;
    font-size: 0.95rem;
    margin-top: 1rem;
    transition: color 0.2s;
}
.card:hover .btn-card-action {
    color: #0284c7;
    text-decoration: underline;
}

.header-logo, .portal-logo, .login-logo {
    max-height: 55px !important;
    height: 50px !important;
    width: auto !important;
    object-fit: contain !important;
}
.navbar {
    background: #006699;
    color: white;
    padding: 0.5rem 2rem !important;
    display: flex;
    justify-content: space-between;
    align-items: center;
    min-height: 75px !important;
}

.card-icon-container {
    margin-bottom: 1.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
}
.card-icon {
    width: 60px;
    height: 60px;
    object-fit: contain;
}

```

---

## Ruta: `frontend\css\style-portal.css`

```
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap');

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

body {
    background-color: #f0f4f8;
    color: #1e293b;
    line-height: 1.5;
    padding-bottom: 3rem;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

.patient-header {
    background-color: #006699;
    color: #ffffff;
    padding: 1rem 1rem !important;
    text-align: center;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.header-inner {
    max-width: 600px;
    margin: 0 auto;
}

.badge-hospital {
    display: inline-block;
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 0.25rem 0.6rem;
    border-radius: 12px;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
}

.patient-header h1 {
    font-size: 1.6rem;
    font-weight: 700;
    letter-spacing: -0.5px;
}

.patient-header p {
    font-size: 0.95rem;
    opacity: 0.9;
    margin-top: 0.25rem;
}

.patient-container {
    flex: 1;
    width: 100%;
    max-width: 600px;
    margin: 0 auto;
    padding: 1.25rem 1rem;
}

.card {
    background: #ffffff;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
    border: 1px solid #e2e8f0;
    margin-bottom: 1.25rem;
}

.verification-card {
    border-top: 4px solid #0284c7;
}

.verification-card h2 {
    font-size: 1.15rem;
    color: #0f172a;
    margin-bottom: 0.4rem;
}

.verification-card p {
    font-size: 0.85rem;
    color: #64748b;
    margin-bottom: 1rem;
    line-height: 1.4;
}

.verification-form .form-row {
    display: flex;
    gap: 0.5rem;
}

.verification-form input {
    flex-grow: 1;
    padding: 0.7rem 0.9rem;
    font-size: 1rem;
    border: 1.5px solid #cbd5e1;
    border-radius: 8px;
    outline: none;
}

.verification-form input:focus {
    border-color: #0284c7;
}

.btn-verify {
    background-color: #0284c7;
    color: white;
    border: none;
    padding: 0 1.25rem;
    font-weight: 600;
    border-radius: 8px;
    cursor: pointer;
}

.btn-verify:hover {
    background-color: #0369a1;
}

.alerta-inline {
    background: #fee2e2;
    color: #991b1b;
    padding: 0.6rem;
    border-radius: 6px;
    font-size: 0.85rem;
    margin-top: 0.5rem;
}

.alerta-inline.oculta { display: none; }

.welcome-box {
    background-color: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #166534;
    padding: 1rem;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    align-items: flex-start;
}

.welcome-icon {
    font-size: 1.5rem;
    color: #15803d;
}

.welcome-box p {
    margin-bottom: 0;
    font-size: 0.9rem;
    color: #166534;
}

.btn-link-logout {
    background: none;
    border: none;
    color: #15803d;
    text-decoration: underline;
    font-size: 0.8rem;
    cursor: pointer;
    font-weight: 600;
    padding: 0;
}

.filters-card {
    padding: 1rem;
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    margin-bottom: 1.25rem;
}

.search-box input {
    width: 100%;
    padding: 0.7rem 1rem;
    font-size: 0.95rem;
    border: 1.5px solid #cbd5e1;
    border-radius: 8px;
    outline: none;
    margin-bottom: 0.75rem;
}

.search-box input:focus {
    border-color: #006699;
}

.category-tabs {
    display: flex;
    gap: 0.4rem;
    overflow-x: auto;
    padding-bottom: 0.25rem;
}

.tab-btn {
    background: #f1f5f9;
    border: none;
    padding: 0.4rem 0.8rem;
    font-size: 0.8rem;
    font-weight: 600;
    color: #475569;
    border-radius: 20px;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s;
}

.tab-btn.active {
    background-color: #006699;
    color: white;
}

.documents-grid {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.doc-card {
    background: #ffffff;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    padding: 1.25rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    transition: transform 0.2s;
}

.doc-card:hover {
    transform: translateY(-2px);
}

.doc-info-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.doc-badge-cat {
    background-color: #f1f5f9;
    color: #475569;
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    text-transform: uppercase;
}

.doc-badge-sensible {
    background-color: #fee2e2;
    color: #991b1b;
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
}

.doc-badge-publico {
    background-color: #dcfce7;
    color: #15803d;
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
}

.doc-card h3 {
    font-size: 1.05rem;
    color: #0f172a;
    font-weight: 600;
    line-height: 1.4;
}

.doc-card-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.25rem;
}

.btn-doc-download {
    flex-grow: 1;
    background-color: #006699;
    color: white;
    text-decoration: none;
    text-align: center;
    padding: 0.6rem;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
}

.btn-doc-download:hover {
    background-color: #004d73;
}

.btn-doc-locked {
    flex-grow: 1;
    background-color: #94a3b8;
    color: white;
    text-decoration: none;
    text-align: center;
    padding: 0.6rem;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
}

.btn-doc-locked:hover {
    background-color: #64748b;
}

.btn-survey-trigger {
    background-color: #0284c7;
    color: white;
    border: none;
    padding: 0.6rem 0.85rem;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
}

.btn-survey-trigger:hover {
    background-color: #0369a1;
}

.loader-box {
    text-align: center;
    padding: 3rem 1rem;
}

.spinner {
    width: 36px;
    height: 36px;
    border: 3.5px solid #cbd5e1;
    border-top: 3.5px solid #006699;
    border-radius: 50%;
    margin: 0 auto 1rem;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.oculta { display: none !important; }

/* Modal de las encuestas */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(15, 23, 42, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    z-index: 2000;
}

.modal-content {
    background: #ffffff;
    width: 100%;
    max-width: 500px;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15);
    display: flex;
    flex-direction: column;
    max-height: 90vh;
}

.modal-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #f8fafc;
}

.header-title-box {
    text-align: left;
}

.survey-badge {
    display: inline-block;
    background-color: #e0f2fe;
    color: #0369a1;
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    text-transform: uppercase;
    margin-bottom: 0.25rem;
}

.modal-header h3 {
    font-size: 1.15rem;
    color: #0f172a;
    font-weight: 700;
}

.btn-close {
    background: none;
    border: none;
    font-size: 1.6rem;
    color: #64748b;
    cursor: pointer;
    line-height: 1;
}

.survey-form {
    padding: 1.5rem;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.questions-container {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.question-block {
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 1rem;
    border-radius: 8px;
}

.question-text {
    font-size: 0.9rem;
    font-weight: 600;
    color: #334155;
    margin-bottom: 0.75rem;
    line-height: 1.4;
}

/* Opciones Si/No */
.radio-options-row {
    display: flex;
    gap: 1.5rem;
}

.option-radio-label {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.9rem;
    color: #475569;
    cursor: pointer;
}

.option-radio-label input {
    cursor: pointer;
}

/* Escala del 1 al 5 */
.scale-options-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.4rem;
    margin-top: 0.5rem;
}

.scale-label {
    font-size: 0.75rem;
    color: #64748b;
    font-weight: 600;
}

.scale-items {
    display: flex;
    gap: 0.5rem;
}

.scale-item-btn {
    border: 1.5px solid #cbd5e1;
    background: white;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    font-weight: bold;
    color: #475569;
    cursor: pointer;
    transition: all 0.2s;
}

.scale-item-btn:hover {
    border-color: #0284c7;
    color: #0284c7;
}

.scale-item-btn.selected {
    background-color: #0284c7;
    border-color: #0284c7;
    color: white;
}

/* Texto libre / Observaciones */
.text-area-group {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.text-area-group label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #475569;
}

.text-area-group textarea,
.question-block textarea {
    width: 100%;
    padding: 0.6rem 0.8rem;
    border: 1.5px solid #cbd5e1;
    border-radius: 6px;
    font-size: 0.9rem;
    outline: none;
    resize: vertical;
}

.text-area-group textarea:focus,
.question-block textarea:focus {
    border-color: #0284c7;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    border-top: 1px solid #e2e8f0;
    padding-top: 1rem;
    margin-top: 0.5rem;
}

.btn-secondary {
    background-color: #e2e8f0;
    color: #334155;
    border: none;
    padding: 0.65rem 1.2rem;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
}

.btn-secondary:hover { background-color: #cbd5e1; }

.btn-primary {
    background-color: #0284c7;
    color: white;
    border: none;
    padding: 0.65rem 1.2rem;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
}

.btn-primary:hover { background-color: #0369a1; }

.patient-footer {
    text-align: center;
    padding: 2rem 1rem;
    font-size: 0.75rem;
    color: #94a3b8;
    border-top: 1px solid #e2e8f0;
    margin-top: auto;
}

.portal-logo-container {
    text-align: center;
    margin-bottom: 1rem;
}
.portal-logo {
    height: 60px;
    max-width: 100%;
    object-fit: contain;
    display: inline-block;
}

.header-logo, .portal-logo, .login-logo {
    max-height: 60px !important;
    height: 55px !important;
    width: auto !important;
    object-fit: contain !important;
}

/* Footer layout */
body {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}
.app-footer {
    text-align: center;
    padding: 1.5rem;
    margin-top: auto; 
    border-top: 1px solid #e2e8f0;
    color: #64748b;
    font-size: 0.8rem;
    background-color: #ffffff;
    width: 100%;
    box-sizing: border-box;
}

.portal-logo {
    height: 95px !important;
    max-height: 95px !important;
    margin-bottom: 0.75rem;
}

.portal-logo {
    height: 95px !important;
    max-height: 95px !important;
    margin-bottom: 0.75rem;
}

.header-logo, .portal-logo, .login-logo {
    max-height: 100px !important;
    height: 85px !important;
    width: auto !important;
    object-fit: contain !important;
}
.navbar {
    min-height: 135px !important;
}

/* Botón de volver al login */
.btn-back-login {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background-color: rgba(255, 255, 255, 0.12);
    color: #ffffff;
    border: 1px solid rgba(255, 255, 255, 0.25);
    padding: 0.45rem 0.9rem;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease-in-out;
    z-index: 100;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}

.btn-back-login:hover {
    background-color: #ffffff;
    color: #1e3a8a; 
    border-color: #ffffff;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Ajuste para celulares */
@media (max-width: 576px) {
    .patient-header .header-inner {
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    
    .btn-back-login {
        position: static;
        margin-bottom: 1.25rem;
        width: 100%;
        justify-content: center;
    }
}
```

---

## Ruta: `frontend\html\ambulancias-chofer.html`

```
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terminal Móvil de Chofer - S.I.G.S.M.</title>
    <link rel="stylesheet" href="../css/style-portal.css">
    <link rel="stylesheet" href="../css/style-ambulancias.css">
</head>
<body class="mobile-body">
    <div class="mobile-frame">
        <header class="mobile-header">
            <img src="../img/HospitalDeClinicasWhite.png" alt="Hospital de Clínicas Logo" class="mobile-logo">
            <h2>Terminal Móvil</h2>
            <p>Módulo de Trazabilidad para Choferes</p>
        </header>

        <main class="mobile-container">
            <section class="card select-driver-card">
                <label for="selectChoferActivo"><strong>Identificarse como Chofer:</strong></label>
                <select id="selectChoferActivo"></select>
                <small style="color:#64748b;">Sesión: <span id="txtNombre">...</span> <span id="txtRol" class="oculta"></span></small>
                <div style="display:flex; gap:0.5rem; margin-top:0.5rem;">
                    <a href="ambulancias.html" class="btn-mobile" style="text-align:center; text-decoration:none; flex:1;">← Volver a Traslados</a>
                    <button id="btnCerrarSesion" class="btn-mobile" style="flex:1;">Cerrar Sesión</button>
                </div>
            </section>

            <div id="sinViajeBox" class="card text-center py-4">
                <div class="no-travel-spinner"></div>
                <h4 style="margin-top: 1rem; color: #475569;">Sin viajes asignados</h4>
                <p style="font-size: 0.85rem; color: #64748b; margin-top: 0.5rem;">Cuando el área de transporte asigne un traslado a este chofer aparecerá aquí.</p>
            </div>

            <div id="viajeAsignadoBox" class="card travel-details-card oculta">
                <div class="travel-header">
                    <span id="badgePrioridadChofer" class="badge-prioridad">ALTA</span>
                    <h4>Viaje ID: <span id="txtViajeId">#1</span></h4>
                </div>

                <div class="travel-route">
                    <div class="route-point">
                        <span class="route-icon route-start"><span>A</span></span>
                        <div class="route-desc">
                            <small>ORIGEN</small>
                            <strong id="txtViajeOrigen">...</strong>
                        </div>
                    </div>
                    <div class="route-line"></div>
                    <div class="route-point">
                        <span class="route-icon route-end"><span>B</span></span>
                        <div class="route-desc">
                            <small>DESTINO</small>
                            <strong id="txtViajeDestino">...</strong>
                        </div>
                    </div>
                </div>

                <div class="travel-meta">
                    <p><strong>Paciente / Objeto:</strong> <span id="txtViajePaciente">...</span></p>
                    <p><strong>Salida:</strong> <span id="txtViajeHorario">...</span></p>
                    <p><strong>Vehículo:</strong> <span id="txtViajeVehiculo">...</span></p>
                    <p><strong>Enfermero:</strong> <span id="txtViajeEnfermero">...</span></p>
                    <p><strong>Estado Actual:</strong> <span id="txtViajeEstado" class="badge-estado">...</span></p>
                </div>

                <div id="msgChofer" role="alert" style="color:#b91c1c; font-size:0.85rem;"></div>

                <div class="travel-actions" id="actionsPendiente">
                    <button id="btnIniciarViaje" class="btn-mobile btn-mobile-primary">Iniciar Traslado</button>
                </div>
                <div class="travel-actions oculta" id="actionsEnCurso">
                    <button id="btnRegistrarLlegada" class="btn-mobile btn-mobile-success">Registrar Llegada a Destino</button>
                </div>
                <div class="travel-actions oculta" id="actionsRetornando">
                    <button id="btnFinalizarViaje" class="btn-mobile btn-mobile-success">Registrar Retorno al Hospital</button>
                </div>
            </div>
        </main>

        <footer class="mobile-footer text-center">
            <p>Copyright &copy; 2026 Academia Sigma. Todos los derechos reservados.</p>
        </footer>
    </div>

    <script src="../js/auth.js"></script>
    <script src="../js/ambulancias-chofer.js"></script>
</body>
</html>

```

---

## Ruta: `frontend\html\ambulancias-recursos.html`

```
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S.I.G.S.M. - Choferes, Enfermeros y Flota</title>
    <link rel="stylesheet" href="../css/style-documentos.css">
    <link rel="stylesheet" href="../css/style-ambulancias.css">
</head>
<body>
    <header class="navbar">
        <div class="brand">
            <img src="../img/HospitalDeClinicasWhite.png" alt="Hospital de Clínicas Logo" class="header-logo">
        </div>
        <div class="user-info">
            <span id="txtNombre">Cargando...</span>
            <span id="txtRol" class="badge-rol">...</span>
            <a href="ambulancias.html" class="btn-nav">Volver a Traslados</a>
            <button id="btnCerrarSesion" class="btn-logout">Cerrar Sesión</button>
        </div>
    </header>

    <main class="container">
        <div style="display:flex; gap:0.5rem; margin-bottom:1rem; flex-wrap:wrap;">
            <button class="btn-primary btn-small tab-recurso" data-recurso="choferes">Choferes</button>
            <button class="btn-secondary btn-small tab-recurso" data-recurso="enfermeros">Enfermeros</button>
            <button class="btn-secondary btn-small tab-recurso" data-recurso="vehiculos">Vehículos</button>
        </div>

        <div class="ambulancias-grid">
            <section class="list-card">
                <div class="list-header"><h3 id="tituloLista">Choferes</h3></div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead><tr id="encabezadoTabla"></tr></thead>
                        <tbody id="cuerpoTabla"></tbody>
                    </table>
                </div>
            </section>

            <section class="form-card">
                <h3 id="tituloForm">Nuevo chofer</h3>
                <form id="formRecurso" class="styled-form" novalidate>
                    <div id="camposForm"></div>
                    <div id="msgRecurso" role="alert" style="color:#b91c1c; font-size:0.85rem; margin-bottom:0.5rem;"></div>
                    <button type="submit" id="btnGuardarRecurso" class="btn-primary w-100">Guardar</button>
                    <button type="button" id="btnCancelarRecurso" class="btn-secondary w-100 oculta" style="margin-top:0.5rem;">Cancelar edición</button>
                </form>
            </section>
        </div>
    </main>

    <script src="../js/auth.js"></script>
    <script src="../js/ambulancias-recursos.js"></script>
    <footer class="app-footer">
        <p>Copyright &copy; 2026 Academia Sigma. Todos los derechos reservados.</p>
    </footer>
</body>
</html>

```

---

## Ruta: `frontend\html\ambulancias.html`

```
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S.I.G.S.M. - Control de Traslados</title>
    <link rel="stylesheet" href="../css/style-documentos.css">
    <link rel="stylesheet" href="../css/style-ambulancias.css">
</head>
<body>
    <header class="navbar">
        <div class="brand">
            <img src="../img/HospitalDeClinicasWhite.png" alt="Hospital de Clínicas Logo" class="header-logo">
        </div>
        <div class="user-info">
            <span id="txtNombre">Cargando...</span>
            <span id="txtRol" class="badge-rol">...</span>
            <a href="ambulancias-recursos.html" id="lnkRecursos" class="btn-nav oculta">Choferes / Enfermeros / Flota</a>
            <a href="panel.html" class="btn-nav">Volver al Panel</a>
            <button id="btnCerrarSesion" class="btn-logout">Cerrar Sesión</button>
        </div>
    </header>

    <main class="container">
        <section class="stats-grid">
            <div class="stat-card stat-total">
                <div class="stat-icon-container"><img src="../img/stat-total.svg" class="stat-svg-icon" alt="Total"></div>
                <div class="stat-desc"><h3 id="statTotalVal">0</h3><p>Traslados Totales</p></div>
            </div>
            <div class="stat-card stat-pending">
                <div class="stat-icon-container"><img src="../img/stat-pending.svg" class="stat-svg-icon" alt="Pendientes"></div>
                <div class="stat-desc"><h3 id="statPendientesVal">0</h3><p>Pendientes</p></div>
            </div>
            <div class="stat-card stat-progress">
                <div class="stat-icon-container"><img src="../img/stat-progress.svg" class="stat-svg-icon" alt="En Curso"></div>
                <div class="stat-desc"><h3 id="statEnCursoVal">0</h3><p>En Curso / Retornando</p></div>
            </div>
            <div class="stat-card stat-completed">
                <div class="stat-icon-container"><img src="../img/stat-completed.svg" class="stat-svg-icon" alt="Finalizados"></div>
                <div class="stat-desc"><h3 id="statCompletadosVal">0</h3><p>Finalizados</p></div>
            </div>
        </section>

        <div class="ambulancias-grid">
            <!-- Columna izquierda: historial / seguimiento -->
            <section class="list-card">
                <div class="list-header">
                    <h3>Historial y Seguimiento de Traslados</h3>
                    <div class="list-actions">
                        <a href="ambulancias-chofer.html" target="_blank" class="btn-action-highlight oculta" id="lnkChofer">Abrir Terminal de Chofer</a>
                    </div>
                </div>

                <form id="formFiltros" class="filtros-traslados" style="display:flex; flex-wrap:wrap; gap:0.5rem; padding:0.75rem 1rem;">
                    <input type="text" id="filtroDocumento" placeholder="C.I. del paciente" inputmode="numeric" maxlength="12" style="flex:1 1 130px;">
                    <select id="filtroVehiculo" style="flex:1 1 130px;"><option value="">Todos los vehículos</option></select>
                    <select id="filtroEstado" style="flex:1 1 120px;">
                        <option value="">Todos los estados</option>
                        <option>Pendiente</option><option>En Curso</option><option>Retornando</option>
                        <option>Finalizado</option><option>Cancelado</option>
                    </select>
                    <input type="date" id="filtroDesde" title="Desde" style="flex:1 1 130px;">
                    <input type="date" id="filtroHasta" title="Hasta" style="flex:1 1 130px;">
                    <button type="submit" class="btn-primary btn-small">Filtrar</button>
                    <button type="button" id="btnLimpiarFiltros" class="btn-secondary btn-small">Limpiar</button>
                </form>

                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Paciente / Objeto</th>
                                <th>Origen &rarr; Destino</th>
                                <th>Recursos Asignados</th>
                                <th>Horarios</th>
                                <th>Prioridad</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaTrasladosCuerpo"></tbody>
                    </table>
                </div>
            </section>

            <!-- Columna derecha: alta / edición -->
            <section class="form-card" id="seccionFormulario">
                <h3 id="tituloFormulario">Nuevo Traslado</h3>
                <p class="form-subtitle" id="subtituloFormulario">Datos del traslado, origen, destino y horarios.</p>

                <form id="formAltaTraslado" class="styled-form" novalidate>
                    <div class="form-group">
                        <label for="tipo_traslado">Tipo de Traslado *</label>
                        <select id="tipo_traslado" required>
                            <option value="paciente">Paciente Clínico</option>
                            <option value="objeto">Órgano / Material / Insumo</option>
                        </select>
                    </div>

                    <div id="grupo_paciente">
                        <div class="form-row-2">
                            <div class="form-group">
                                <label for="pac_documento">C.I. del paciente *</label>
                                <input type="text" id="pac_documento" inputmode="numeric" maxlength="12" placeholder="Sin puntos ni guiones">
                            </div>
                            <div class="form-group">
                                <label for="pac_nombre">Nombre completo *</label>
                                <input type="text" id="pac_nombre" maxlength="100">
                            </div>
                        </div>
                        <div class="form-row-2">
                            <div class="form-group">
                                <label for="pac_nacimiento">Fecha de nacimiento *</label>
                                <input type="date" id="pac_nacimiento">
                            </div>
                            <div class="form-group">
                                <label for="pac_telefono">Teléfono</label>
                                <input type="text" id="pac_telefono" maxlength="20">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Condiciones clínicas y traslado del paciente</label>
                            <div class="condiciones-grid-2x2">
                                <label class="condicion-chip"><input type="checkbox" id="pac_medios" checked><span>Se maneja por sus medios</span></label>
                                <label class="condicion-chip"><input type="checkbox" id="pac_oxigeno"><span>Requiere oxígeno</span></label>
                                <label class="condicion-chip"><input type="checkbox" id="pac_camilla"><span>Requiere camilla</span></label>
                                <label class="condicion-chip"><input type="checkbox" id="pac_aislamiento"><span>Requiere aislamiento</span></label>
                            </div>
                            <small id="pacienteAviso" style="display:block; color:#64748b; margin-top:0.35rem;">Si la C.I. ya existe, se cargan sus datos automáticamente.</small>
                        </div>
                    </div>

                    <div class="form-group oculta" id="grupo_objeto">
                        <label for="objeto">Órgano / Material *</label>
                        <select id="objeto"><option value="">Seleccione material</option></select>
                    </div>

                    <div class="form-row-2">
                        <div class="form-group">
                            <label for="fecha">Fecha *</label>
                            <input type="date" id="fecha" required>
                        </div>
                        <div class="form-group">
                            <label for="hora_salida">Hora de salida *</label>
                            <input type="time" id="hora_salida" required>
                        </div>
                    </div>
                    <div class="form-row-2">
                        <div class="form-group">
                            <label for="hora_estimada_llegada">Llegada estimada *</label>
                            <input type="time" id="hora_estimada_llegada" required>
                        </div>
                        <div class="form-group">
                            <label for="limite_horario" id="lblLimite">Límite de horario</label>
                            <input type="time" id="limite_horario">
                        </div>
                    </div>

                    <div class="form-row-2">
                        <div class="form-group">
                            <label for="sector_origen">Sector de origen *</label>
                            <input type="text" id="sector_origen" maxlength="100" required>
                        </div>
                        <div class="form-group">
                            <label for="piso_origen">Piso *</label>
                            <input type="text" id="piso_origen" maxlength="20" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="habitacion_origen">Habitación de origen</label>
                        <input type="text" id="habitacion_origen" maxlength="20">
                    </div>

                    <div class="form-row-2">
                        <div class="form-group">
                            <label for="sector_destino">Sector de destino *</label>
                            <input type="text" id="sector_destino" maxlength="100" required>
                        </div>
                        <div class="form-group">
                            <label for="piso_destino">Piso *</label>
                            <input type="text" id="piso_destino" maxlength="20" required>
                        </div>
                    </div>
                    <div class="form-row-2">
                        <div class="form-group">
                            <label for="habitacion_destino">Habitación de destino</label>
                            <input type="text" id="habitacion_destino" maxlength="20">
                        </div>
                        <div class="form-group">
                            <label for="direccion_destino">Dirección de destino *</label>
                            <input type="text" id="direccion_destino" maxlength="200" required placeholder="Zona metropolitana">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="division_solicitante">División solicitante *</label>
                        <input type="text" id="division_solicitante" maxlength="100" required>
                    </div>
                    <div class="form-group">
                        <label for="motivo_procedimiento">Motivo / procedimiento *</label>
                        <input type="text" id="motivo_procedimiento" maxlength="200" required>
                    </div>
                    <div class="form-group">
                        <label for="responsable_recepcion">Quién lo recibe *</label>
                        <input type="text" id="responsable_recepcion" maxlength="100" required>
                    </div>

                    <div class="form-group">
                        <label for="prioridad">Nivel de Prioridad *</label>
                        <select id="prioridad" required>
                            <option value="Baja">Baja (Traslado Común)</option>
                            <option value="Media" selected>Media (Monitoreo Clínico)</option>
                            <option value="Alta">Alta (Urgencia Médica)</option>
                        </select>
                    </div>

                    <!-- Asignación de recursos: solo el área de transporte -->
                    <div id="grupoRecursos">
                        <div class="form-group">
                            <label for="vehiculo">Vehículo</label>
                            <select id="vehiculo"><option value="">Sin asignar</option></select>
                        </div>
                        <div class="form-row-2">
                            <div class="form-group">
                                <label for="chofer">Chofer</label>
                                <select id="chofer"><option value="">Sin asignar</option></select>
                            </div>
                            <div class="form-group" id="grupo_enfermero">
                                <label for="enfermero">Enfermero acompañante</label>
                                <select id="enfermero"><option value="">Sin asignar / no requiere</option></select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="observaciones">Observaciones</label>
                        <textarea id="observaciones" rows="2" maxlength="500"></textarea>
                    </div>

                    <div id="msgFormulario" role="alert" style="color:#b91c1c; font-size:0.85rem; margin-bottom:0.5rem;"></div>

                    <button type="submit" id="btnGuardar" class="btn-primary w-100">Registrar Traslado</button>
                    <button type="button" id="btnCancelarEdicion" class="btn-secondary w-100 oculta" style="margin-top:0.5rem;">Cancelar edición</button>
                </form>
            </section>
        </div>
    </main>

    <script src="../js/auth.js"></script>
    <script src="../js/ambulancias.js"></script>
    <footer class="app-footer">
        <p>Copyright &copy; 2026 Academia Sigma. Todos los derechos reservados.</p>
    </footer>
</body>
</html>

```

---

## Ruta: `frontend\html\documentos.html`

```
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S.I.G.S.M. - Gestión de Documentación</title>
    <link rel="stylesheet" href="../css/style-documentos.css">
    <style>
        .tabs-modulo {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }
        .tab-modulo-btn {
            background-color: #ffffff;
            color: #334155;
            border: 1px solid #cbd5e1;
            padding: 0.55rem 1.1rem;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .tab-modulo-btn.active {
            background-color: #006699;
            color: #ffffff;
            border-color: #006699;
        }
        .seccion-oculta {
            display: none !important;
        }
        .encuesta-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 1.25rem;
            margin-bottom: 1.25rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
        }
        .encuesta-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 0.75rem;
            margin-bottom: 1rem;
        }
        .pregunta-resultado {
            padding: 0.75rem 0;
            border-bottom: 1px dashed #e2e8f0;
        }
        .pregunta-resultado:last-child {
            border-bottom: none;
        }
        .barra-progreso {
            background: #e2e8f0;
            border-radius: 4px;
            height: 10px;
            overflow: hidden;
            margin-top: 0.35rem;
        }
        .barra-progreso > div {
            background: #0284c7;
            height: 100%;
        }
        .lista-textos {
            list-style: disc;
            padding-left: 1.25rem;
            margin-top: 0.35rem;
            font-size: 0.85rem;
            color: #334155;
        }
    </style>
</head>
<body>
    <header class="navbar">
        <div class="brand">
            <img src="../img/HospitalDeClinicasWhite.png" alt="Hospital de Clínicas Logo" class="header-logo">
        </div>
        <div class="user-info">
            <span id="txtNombre">Cargando...</span>
            <span id="txtRol" class="badge" style="background:rgba(255,255,255,0.2); color:#fff;">...</span>
            <a href="portal-invitados.html" target="_blank" class="btn-nav">Portal de Invitados &nearr;</a>
            <a href="panel.html" class="btn-nav">Volver al Panel</a>
            <button id="btnCerrarSesion" class="btn-logout">Cerrar Sesión</button>
        </div>
    </header>

    <main class="container">
        <!-- Navegación interna del módulo: Documentos | Categorías | Resultados de Encuestas -->
        <div class="tabs-modulo">
            <button type="button" class="tab-modulo-btn active" data-vista="documentos">Documentos Clínicos</button>
            <button type="button" class="tab-modulo-btn" data-vista="categorias" id="tabBtnCategorias">Categorías</button>
            <button type="button" class="tab-modulo-btn" data-vista="encuestas">Encuestas y Resultados</button>
        </div>

        <div id="alerta" class="alerta oculta" role="alert"></div>

        <!-- ==================== VISTA 1: DOCUMENTOS ==================== -->
        <div id="vistaDocumentos">
            <section class="toolbar">
                <div class="search-filters">
                    <input 
                        type="text" 
                        id="inputBuscar" 
                        placeholder="Buscar por título de documento..."
                        autocomplete="off"
                    >
                    <select id="selectFiltroCategoria">
                        <option value="0">Todas las categorías</option>
                    </select>
                </div>
                <div class="toolbar-actions">
                    <button id="btnAbrirModalAlta" class="btn-primary">+ Nuevo Documento</button>
                </div>
            </section>

            <section class="table-card">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Título</th>
                                <th>Categoría</th>
                                <th>Autor</th>
                                <th>Archivo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaCuerpo">
                            <tr>
                                <td colspan="6" class="text-center">Cargando documentos...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <!-- ==================== VISTA 2: CATEGORÍAS (CRUD COMPLETO) ==================== -->
        <div id="vistaCategorias" class="seccion-oculta">
            <section class="toolbar">
                <div class="search-filters">
                    <form id="formCategoria" style="display:flex; gap:0.5rem; width:100%; padding:0; align-items:center;" novalidate>
                        <input type="hidden" id="cat_id">
                        <input type="text" id="cat_nombre" maxlength="100" placeholder="Nombre de la nueva categoría institucional..." required>
                        <button type="submit" id="btnGuardarCategoria" class="btn-primary">Crear Categoría</button>
                        <button type="button" id="btnCancelarCategoria" class="btn-secondary-cancel seccion-oculta">Cancelar</button>
                    </form>
                </div>
            </section>

            <section class="table-card">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre de Categoría</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaCategoriasCuerpo">
                            <tr>
                                <td colspan="3" class="text-center">Cargando categorías...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <!-- ==================== VISTA 3: ENCUESTAS Y RESULTADOS ==================== -->
        <div id="vistaEncuestas" class="seccion-oculta">
            <section class="toolbar">
                <div class="search-filters">
                    <select id="selectFiltroEncuestaCategoria">
                        <option value="0">Todas las categorías con encuesta</option>
                    </select>
                </div>
                <div class="toolbar-actions">
                    <button type="button" id="btnAbrirModalNuevaEncuesta" class="btn-primary">+ Nueva Encuesta</button>
                    <button type="button" id="btnRefrescarEncuestas" class="btn-secondary">Actualizar</button>
                </div>
            </section>

            <div id="contenedorResultadosEncuestas">
                <p class="text-center">Cargando resultados de satisfacción...</p>
            </div>
        </div>
    </main>

    <!-- Modal: Crear / Editar Encuesta -->
    <div id="modalEncuestaAdmin" class="modal oculta">
        <div class="modal-content">
            <header class="modal-header">
                <h3 id="tituloModalEncuestaAdmin">Nueva Encuesta</h3>
                <button type="button" class="btn-close" id="btnCerrarModalEncuestaAdmin">&times;</button>
            </header>
            <form id="formEncuestaAdmin" novalidate>
                <input type="hidden" id="enc_admin_id">
                <div class="form-group" id="grupoEncAdminCategoria">
                    <label for="enc_admin_categoria">Categoría Institucional *</label>
                    <select id="enc_admin_categoria" required></select>
                </div>
                <div class="form-group">
                    <label for="enc_admin_comentarios">Título / Descripción de la Encuesta *</label>
                    <input type="text" id="enc_admin_comentarios" maxlength="200" placeholder="Ej. Encuesta del Área Cardiología" required>
                </div>
                <div class="form-group">
                    <label for="enc_admin_estado">Estado de Vigencia *</label>
                    <select id="enc_admin_estado" required>
                        <option value="Activa">Activa (Visible en Portal de Invitados)</option>
                        <option value="Borrador">Borrador (En preparación)</option>
                        <option value="Cerrada">Cerrada (No admite nuevas respuestas)</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary-cancel" id="btnCancelarModalEncuestaAdmin">Cancelar</button>
                    <button type="submit" class="btn-primary">Guardar Encuesta</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Crear / Editar Pregunta de Encuesta -->
    <div id="modalPreguntaAdmin" class="modal oculta">
        <div class="modal-content">
            <header class="modal-header">
                <h3 id="tituloModalPreguntaAdmin">Agregar Pregunta</h3>
                <button type="button" class="btn-close" id="btnCerrarModalPreguntaAdmin">&times;</button>
            </header>
            <form id="formPreguntaAdmin" novalidate>
                <input type="hidden" id="preg_admin_id">
                <input type="hidden" id="preg_admin_id_encuesta">
                <div class="form-group">
                    <label for="preg_admin_enunciado">Enunciado de la Pregunta *</label>
                    <input type="text" id="preg_admin_enunciado" maxlength="300" placeholder="Ej. ¿La información provista fue clara?" required>
                </div>
                <div class="form-group">
                    <label for="preg_admin_tipo">Tipo de Respuesta *</label>
                    <select id="preg_admin_tipo" required>
                        <option value="Si/No">Sí / No</option>
                        <option value="Escala_1_5">Escala de Satisfacción (1 a 5)</option>
                        <option value="Texto Libre">Texto Libre (Comentario abierto)</option>
                        <option value="Opcion_Multiple">Opción Múltiple</option>
                    </select>
                </div>
                <div class="form-group seccion-oculta" id="grupoPregAdminOpciones">
                    <label for="preg_admin_opciones">Opciones (una por línea, mínimo 2) *</label>
                    <textarea id="preg_admin_opciones" rows="3" style="width:100%; padding:0.6rem; border:1px solid #cbd5e1; border-radius:6px;" placeholder="Opción 1&#10;Opción 2"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary-cancel" id="btnCancelarModalPreguntaAdmin">Cancelar</button>
                    <button type="submit" class="btn-primary">Guardar Pregunta</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Formulario de Alta de Documento -->
    <div id="modalAlta" class="modal oculta">
        <div class="modal-content">
            <header class="modal-header">
                <h3>Subir Documento Clínico</h3>
                <button type="button" class="btn-close" id="btnCerrarModalAlta">&times;</button>
            </header>

            <form id="formAltaDocumento" novalidate>
                <div class="form-group">
                    <label for="titulo">Título o Asunto del Documento *</label>
                    <input type="text" id="titulo" name="titulo" maxlength="150" placeholder="Ej. Pauta de preparación nefrología" required>
                </div>

                <div class="form-group">
                    <label for="id_categoria">Categoría Institucional *</label>
                    <select id="id_categoria" name="id_categoria" required>
                        <option value="">Seleccione una categoría</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="archivo">Archivo Adjunto (Formato PDF - máx. 10 MB) *</label>
                    <input type="file" id="archivo" name="archivo" accept=".pdf,application/pdf" required>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary-cancel" id="btnCancelarModalAlta">Cancelar</button>
                    <button type="submit" class="btn-primary" id="btnGuardarDocumento">Guardar Documento</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Formulario de Edición del Documento -->
    <div id="modalEditar" class="modal oculta">
        <div class="modal-content">
            <header class="modal-header">
                <h3>Editar Documento Clínico</h3>
                <button type="button" class="btn-close" id="btnCerrarModalEditar">&times;</button>
            </header>

            <form id="formEditarDocumento" novalidate>
                <input type="hidden" id="edit_id_documento" name="id_documento">

                <div class="form-group">
                    <label for="edit_titulo">Título o Asunto *</label>
                    <input type="text" id="edit_titulo" name="titulo" maxlength="150" required>
                </div>

                <div class="form-group">
                    <label for="edit_id_categoria">Categoría Institucional *</label>
                    <select id="edit_id_categoria" name="id_categoria" required>
                        <option value="">Seleccione una categoría</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="edit_archivo">Reemplazar Archivo PDF (Opcional - Dejar vacío para conservar el actual)</label>
                    <input type="file" id="edit_archivo" name="archivo" accept=".pdf,application/pdf">
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary-cancel" id="btnCancelarModalEditar">Cancelar</button>
                    <button type="submit" class="btn-primary" id="btnActualizarDocumento">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../js/auth.js"></script>
    <script src="../js/documentos.js"></script>
    <footer class="app-footer">
        <p>Copyright &copy; 2026 Academia Sigma. Todos los derechos reservados.</p>
    </footer>
</body>
</html>

```

---

## Ruta: `frontend\html\panel.html`

```
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S.I.G.S.M. - Panel Principal</title>
    <link rel="stylesheet" href="../css/style-panel.css">
    <style>
        /* Ajuste responsivo para mostrar 3 tarjetas si es administrador */
        @media (min-width: 768px) {
            .cards-grid {
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)) !important;
            }
        }
    </style>
</head>
<body>
    <header class="navbar">
        <div class="brand">
            <img src="../img/HospitalDeClinicasWhite.png" alt="Hospital de Clínicas Logo" class="header-logo">
        </div>
        <div class="user-info">
            <span id="txtNombre">Cargando...</span>
            <span id="txtRol" class="badge-rol">...</span>
            <button id="btnCerrarSesion" class="btn-logout">Cerrar Sesión</button>
        </div>
    </header>

    <main class="container">
        <section class="welcome-box">
            <h1>Bienvenido al Sistema de Gestión</h1>
            <p>Seleccione uno de los servicios disponibles en el menú:</p>
            
            <div class="cards-grid">
                <article class="card">
                    <div class="card-icon-container">
                        <img src="../img/card-documentos.svg" class="card-icon" alt="Documentación">
                    </div>
                    <h3>Documentación Clínica y Encuestas</h3>
                    <p>Subida y gestión de directivas médicas, categorías institucionales y resultados de satisfacción de pacientes.</p>
                    <a href="documentos.html" class="btn-card-action">
                        Administrar Documentos &rarr;
                    </a>
                </article>

                <article class="card card-users" id="cardUsuarios" style="display: none;">
                    <div class="card-icon-container">
                        <img src="../img/card-usuarios.svg" class="card-icon" alt="Funcionarios">
                    </div>
                    <h3>Gestión de Funcionarios</h3>
                    <p>Control de acceso, alta, baja y asignación de roles de seguridad para el personal del hospital.</p>
                    <a href="usuarios.html" class="btn-card-action">
                        Administrar Personal &rarr;
                    </a>
                </article>

                <article class="card">
                    <div class="card-icon-container">
                        <img src="../img/card-traslados.svg" class="card-icon" alt="Traslados">
                    </div>
                    <h3>Gestión de Traslados</h3>
                    <p>Planificación, despacho y trazabilidad en tiempo real de traslados sanitarios de pacientes, insumos y órganos.</p>
                    <a href="ambulancias.html" class="btn-card-action">
                        Administrar Traslados &rarr;
                    </a>
                </article>
            </div>
        </section>
    </main>

    <script src="../js/auth.js"></script>
    <script src="../js/panel.js"></script>
    <footer class="app-footer">
        <p>Copyright &copy; 2026 Academia Sigma. Todos los derechos reservados.</p>
    </footer>
</body>
</html>

```

---

## Ruta: `frontend\html\portal-invitados.html`

```
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S.I.G.S.M. - Portal del Paciente</title>
    <link rel="stylesheet" href="../css/style-portal.css">
</head>
<body>
    <header class="patient-header">
        <div class="header-inner" style="position: relative;"> 
            <!-- Botón para volver al login de funcionarios -->
            <a href="../index.html" class="btn-back-login">
                &larr; Volver al Acceso
            </a>

            <div class="portal-logo-container">
                <img src="../img/HospitalDeClinicasWhite.png" alt="Hospital de Clínicas Logo" class="portal-logo">
            </div>
            <h1>Portal de Invitados</h1>
            <p>Guías e Indicaciones Médicas Digitales</p>
        </div>
    </header>

    <main class="patient-container">
        <!-- Card informativa de bienvenida (Sin validaciones) -->
        <section class="card verification-card">
            <h2>Bienvenido al Portal de Pacientes</h2>
            <p>Aquí podrá consultar, visualizar y descargar de forma libre y directa todas las guías institucionales, directivas de preparación para estudios clínicos e indicaciones médicas proporcionadas por el Hospital de Clínicas.</p>
        </section>

        <!-- Barra de Búsqueda y Filtro de Categorías -->
        <section class="filters-card">
            <div class="search-box">
                <input 
                    type="text" 
                    id="inputBuscar" 
                    placeholder="Buscar guías médicas (ej: IVE, Preparación)..."
                    autocomplete="off"
                >
            </div>
            <div class="category-tabs" id="categoryTabs">
                <button class="tab-btn active" data-category="0">Todos</button>
            </div>
        </section>

        <!-- Listado Dinámico de Documentos -->
        <section class="documents-grid" id="documentsGrid">
            <div class="loader-box">
                <div class="spinner"></div>
                <p>Cargando catálogo de documentos...</p>
            </div>
        </section>

        <!-- Encuestas de Satisfacción Activas -->
        <section class="card" id="activeSurveysSection" style="margin-top: 1.5rem;">
            <h2 style="font-size: 1.1rem; color: #0f172a; margin-bottom: 0.35rem;">Encuestas de Satisfacción Disponibles</h2>
            <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 1rem;">Su opinión anónima nos ayuda a mejorar la calidad de atención en cada área del Hospital de Clínicas.</p>
            <div id="activeSurveysGrid" class="documents-grid"></div>
        </section>
    </main>

    <!-- Modal dinámico e las encuestas de satisfacción -->
    <div id="modalEncuesta" class="modal oculta">
        <div class="modal-content">
            <header class="modal-header">
                <div class="header-title-box">
                    <span class="survey-badge">Encuesta de Satisfacción Anónima</span>
                    <h3 id="surveyTitle">...</h3>
                </div>
                <button type="button" class="btn-close" id="btnCerrarModalEncuesta">&times;</button>
            </header>
            
            <form id="formEncuesta" class="survey-form">
                <input type="hidden" id="survey_id_encuesta">
                
                <!-- Acá las preguntas se inyectarán dinámicamente  -->
                <div id="surveyQuestionsContainer" class="questions-container"></div>
                
                <div class="form-group text-area-group">
                    <label for="survey_observaciones">Observaciones generales (Opcional):</label>
                    <textarea id="survey_observaciones" rows="3" placeholder="Si lo desea, déjenos un comentario detallado sobre su experiencia..."></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" id="btnCancelarEncuesta">Cancelar</button>
                    <button type="submit" class="btn-primary" id="btnEnviarEncuesta">Enviar Respuestas</button>
                </div>
            </form>
        </div>
    </div>

    <footer class="patient-footer">
        <p>Sistema Informático de Gestión de Servicios Médicos (S.I.G.S.M.)</p>
        <p>Hospital de Clínicas Dr. Manuel Quintela | Montevideo, Uruguay</p>
        <p style="margin-top: 0.5rem; font-size: 0.75rem; opacity: 0.8;">Copyright &copy; 2026 Academia Sigma. Todos los derechos reservados.</p>
    </footer>

    <script src="../js/portal-invitados.js"></script>
</body>
</html>

```

---

## Ruta: `frontend\html\usuarios.html`

```
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S.I.G.S.M. - Gestión de Funcionarios</title>
    <link rel="stylesheet" href="../css/style-documentos.css">
    <style>
        .badge-rol {
            background-color: #2563eb;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
            margin-right: 1rem;
        }
        .btn-nav {
            background-color: transparent;
            color: var(--primary);
            border: 1px solid var(--primary);
            padding: 0.4rem 0.8rem;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            margin-right: 0.5rem;
        }
        .btn-nav:hover {
            background-color: var(--primary);
            color: white;
        }
        .badge-status-activo {
            background-color: #dcfce7;
            color: #166534;
            padding: 0.2rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: bold;
        }
        .badge-status-inactivo {
            background-color: #fee2e2;
            color: #991b1b;
            padding: 0.2rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <header class="navbar">
        <div class="brand">
            <img src="../img/HospitalDeClinicasWhite.png" alt="Hospital de Clínicas Logo" class="header-logo">
        </div>
        <div class="user-info">
            <span id="txtNombre">Cargando...</span>
            <span id="txtRol" class="badge-rol">...</span>
            <a href="panel.html" class="btn-nav">Volver al Panel</a>
            <button id="btnCerrarSesion" class="btn-logout">Cerrar Sesión</button>
        </div>
    </header>

    <main class="container">
        <section class="toolbar" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; gap: 1rem; flex-wrap: wrap;">
            <div class="search-filters" style="flex: 1; min-width: 250px;">
                <input 
                    type="text" 
                    id="inputBuscar" 
                    placeholder="Buscar funcionario por nombre o email..."
                    autocomplete="off"
                    style="width: 100%; max-width: 400px; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 4px;"
                >
            </div>
            <div class="toolbar-actions">
                <button id="btnAbrirModalAlta" class="btn-primary" style="display: none;">+ Nuevo Funcionario</button>
            </div>
        </section>

        <div id="alerta" class="alerta oculta" role="alert" style="margin-bottom: 1rem; padding: 0.75rem 1rem; border-radius: 4px; font-weight: bold;"></div>

        <section class="table-card">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre Completo</th>
                            <th>Email Institucional</th>
                            <th>Rol de Acceso</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaCuerpo">
                        <tr>
                            <td colspan="6" class="text-center">Cargando personal...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <!-- Modal: Formulario de Alta de Funcionario -->
    <div id="modalAlta" class="modal oculta">
        <div class="modal-content">
            <header class="modal-header">
                <h3>Registrar Funcionario Institucional</h3>
                <button type="button" class="btn-close" id="btnCerrarModalAlta">&times;</button>
            </header>

            <form id="formAltaUsuario" novalidate>
                <div class="form-group">
                    <label for="nombre">Nombre Completo *</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Ej. Dr. Esteban Quito" required>
                </div>

                <div class="form-group">
                    <label for="email">Correo Electrónico Institucional *</label>
                    <input type="email" id="email" name="email" placeholder="usuario@hc.edu.uy" required>
                </div>

                <div class="form-group">
                    <label for="contrasenia">Contraseña Temporal *</label>
                    <input type="password" id="contrasenia" name="contrasenia" placeholder="Mínimo 6 caracteres" required>
                </div>

                <div class="form-group">
                    <label for="rol">Rol y Nivel de Acceso *</label>
                    <select id="rol" name="rol" required>
                        <option value="">Seleccione un rol</option>
                        <option value="SUPERADMIN_IT">SUPERADMIN_IT (Administrador TI)</option>
                        <option value="GESTOR_INTEGRAL">GESTOR_INTEGRAL (Gestor de Servicios)</option>
                        <option value="DIRECTOR_GENERAL">DIRECTOR_GENERAL (Director Médico)</option>
                        <option value="USUARIO_DIVISION">USUARIO_DIVISION (Personal Clínico)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="estado">Estado Inicial *</label>
                    <select id="estado" name="estado" required>
                        <option value="Activo">Activo (Habilitado)</option>
                        <option value="Inactivo">Inactivo (Suspendido)</option>
                    </select>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" id="btnCancelarModalAlta">Cancelar</button>
                    <button type="submit" class="btn-primary" id="btnGuardarUsuario">Crear Funcionario</button>
                </div>
            </form>
        </div>
    </div>

    
    <!-- Modal: Formulario de modificación de funcionario -->
    <div id="modalEditar" class="modal oculta">
        <div class="modal-content">
            <header class="modal-header">
                <h3>Registrar Cambios de Funcionario</h3>
                <button type="button" class="btn-close" id="btnCerrarModalEditar">&times;</button>
            </header>

            <form id="formEditarUsuario" novalidate>
                <input type="hidden" id="edit_id_usuario" name="id_usuario">
                <div class="form-group">
                    <label for="edit_nombre">Nombre Completo *</label>
                    <input type="text" id="edit_nombre" name="nombre" placeholder="Ej. Dr. Esteban Quito" required>
                </div>

                <div class="form-group">
                    <label for="edit_email">Correo Electrónico Institucional *</label>
                    <input type="email" id="edit_email" name="email" placeholder="usuario@hc.edu.uy" required>
                </div>

                <div class="form-group">
                    <label for="edit_contrasenia">Contraseña (Dejar en blanco para no modificar)</label>
                    <input type="password" id="edit_contrasenia" name="contrasenia" placeholder="Mínimo 6 caracteres (Opcional)">
                </div>

                <div class="form-group">
                    <label for="edit_rol">Rol y Nivel de Acceso *</label>
                    <select id="edit_rol" name="rol" required>
                        <option value="">Seleccione un rol</option>
                        <option value="SUPERADMIN_IT">SUPERADMIN_IT (Administrador TI)</option>
                        <option value="GESTOR_INTEGRAL">GESTOR_INTEGRAL (Gestor de Servicios)</option>
                        <option value="DIRECTOR_GENERAL">DIRECTOR_GENERAL (Director Médico)</option>
                        <option value="USUARIO_DIVISION">USUARIO_DIVISION (Personal Clínico)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="edit_estado">Estado *</label>
                    <select id="edit_estado" name="estado" required>
                        <option value="Activo">Activo (Habilitado)</option>
                        <option value="Inactivo">Inactivo (Suspendido)</option>
                    </select>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" id="btnCancelarModalEditar">Cancelar</button>
                    <button type="submit" class="btn-primary" id="btnActualizarUsuario">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../js/auth.js"></script>
    <script src="../js/usuarios.js"></script>
    <footer class="app-footer">
        <p>Copyright &copy; 2026 Academia Sigma. Todos los derechos reservados.</p>
    </footer>
</body>
</html>

```

---

## Ruta: `frontend\js\ambulancias-chofer.js`

```javascript
/**
 * Terminal móvil del chofer. Muestra el viaje activo (En Curso / Retornando) o el próximo
 * Pendiente del chofer elegido y permite registrar el avance: salida, llegada a destino y retorno.
 * Usa la API REST; cada acción llama a PUT /api/traslados/{id}/estado.
 */
document.addEventListener('DOMContentLoaded', async () => {
    const API = '../../api';
    const $ = (sel) => document.querySelector(sel);

    const usuario = await window.authPromise;
    if (!usuario) return;

    const selectChofer = $('#selectChoferActivo');
    let viajeActivo = null;

    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) =>
        ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    async function llamar(url, metodo = 'GET', cuerpo = null) {
        const opciones = { method: metodo, headers: {} };
        if (cuerpo !== null) {
            opciones.headers['Content-Type'] = 'application/json';
            opciones.body = JSON.stringify(cuerpo);
        }
        try {
            const res = await fetch(url, opciones);
            return { ok: res.ok, data: await res.json().catch(() => ({})) };
        } catch (e) {
            return { ok: false, data: { mensaje: 'No se pudo conectar con el servidor.' } };
        }
    }

    async function cargarChoferes() {
        const r = await llamar(`${API}/choferes/index.php`);
        const choferes = r.ok ? r.data.datos : [];
        selectChofer.innerHTML = choferes.map((c) => `<option value="${esc(c.id_chofer)}">${esc(c.nombre)}</option>`).join('');
    }

    async function buscarViajeAsignado() {
        $('#msgChofer').textContent = '';
        viajeActivo = null;
        if (selectChofer.value) {
            const r = await llamar(`${API}/traslados/index.php?id_chofer=${encodeURIComponent(selectChofer.value)}`);
            const lista = r.ok ? r.data.datos : [];
            // Prioriza el viaje ya iniciado; si no hay, el próximo pendiente (el listado viene del más reciente al más antiguo).
            viajeActivo = lista.find((t) => t.estado === 'En Curso' || t.estado === 'Retornando')
                || lista.filter((t) => t.estado === 'Pendiente').pop()
                || null;
        }
        mostrarViaje();
    }

    function mostrarViaje() {
        const hay = viajeActivo !== null;
        $('#sinViajeBox').classList.toggle('oculta', hay);
        $('#viajeAsignadoBox').classList.toggle('oculta', !hay);
        if (!hay) return;

        const t = viajeActivo;
        $('#txtViajeId').textContent = `#${t.id_traslado}`;
        $('#txtViajeOrigen').textContent = `${t.sector_origen} (Piso ${t.piso_origen}${t.habitacion_origen ? ', hab. ' + t.habitacion_origen : ''})`;
        $('#txtViajeDestino').textContent = `${t.sector_destino} - ${t.direccion_destino}`;
        $('#txtViajePaciente').textContent = t.id_paciente ? t.paciente_nombre : `${t.objeto_nombre} (Objeto)`;
        $('#txtViajeHorario').textContent = `${t.fecha} ${t.hora_salida.substring(0, 5)} (llegada est. ${t.hora_estimada_llegada.substring(0, 5)})`;
        $('#txtViajeVehiculo').textContent = t.vehiculo_matricula ? `${t.tipo_vehiculo} ${t.vehiculo_matricula}` : 'Sin asignar';
        $('#txtViajeEnfermero').textContent = t.enfermero_nombre || 'No requiere';

        const badge = $('#badgePrioridadChofer');
        badge.textContent = t.prioridad.toUpperCase();
        badge.className = 'badge-prioridad ' + ({ Alta: 'badge-prio-alta', Media: 'badge-prio-media' }[t.prioridad] || 'badge-prio-baja');

        const estado = $('#txtViajeEstado');
        estado.textContent = t.estado;
        estado.className = 'badge-estado ' + (t.estado === 'Pendiente' ? 'status-yellow' : 'status-blue');

        $('#actionsPendiente').classList.toggle('oculta', t.estado !== 'Pendiente');
        $('#actionsEnCurso').classList.toggle('oculta', t.estado !== 'En Curso');
        $('#actionsRetornando').classList.toggle('oculta', t.estado !== 'Retornando');
    }

    async function cambiarEstado(nuevoEstado) {
        if (!viajeActivo) return;
        const r = await llamar(`${API}/traslados/index.php?id=${viajeActivo.id_traslado}&accion=estado`, 'PUT', { estado: nuevoEstado });
        if (!r.ok) {
            $('#msgChofer').textContent = (r.data.errores && r.data.errores.join(' ')) || r.data.mensaje || 'No se pudo actualizar el estado.';
            return;
        }
        if (nuevoEstado === 'Finalizado') alert('Traslado finalizado. Registro guardado en el historial.');
        await buscarViajeAsignado();
    }

    selectChofer.addEventListener('change', buscarViajeAsignado);
    $('#btnIniciarViaje').addEventListener('click', () => cambiarEstado('En Curso'));
    $('#btnRegistrarLlegada').addEventListener('click', () => cambiarEstado('Retornando'));
    $('#btnFinalizarViaje').addEventListener('click', () => cambiarEstado('Finalizado'));

    await cargarChoferes();
    await buscarViajeAsignado();
    setInterval(buscarViajeAsignado, 30000);
});

```

---

## Ruta: `frontend\js\ambulancias-recursos.js`

```javascript
/**
 * Gestión de choferes, enfermeros y vehículos (CRUD completo sobre la API REST).
 * Una única pantalla con pestañas: cada recurso se describe en la configuración CONFIG.
 * Acceso: solo SUPERADMIN_IT y GESTOR_INTEGRAL (el backend también lo exige).
 */
document.addEventListener('DOMContentLoaded', async () => {
    const API = '../../api';
    const $ = (sel) => document.querySelector(sel);

    const usuario = await window.authPromise;
    if (!usuario) return;
    if (!['SUPERADMIN_IT', 'GESTOR_INTEGRAL'].includes(usuario.rol)) {
        window.location.href = 'ambulancias.html';
        return;
    }

    const CONFIG = {
        choferes: {
            etiqueta: 'chofer', pk: 'id_chofer',
            campos: [
                { id: 'nombre', texto: 'Nombre completo', tipo: 'text', max: 100 },
                { id: 'telefono', texto: 'Teléfono', tipo: 'text', max: 20, patron: /^[0-9+\s-]{7,20}$/, ayuda: 'Solo números, +, espacios y guiones.' },
                { id: 'licencia', texto: 'Licencia de conducir', tipo: 'text', max: 50 }
            ]
        },
        enfermeros: {
            etiqueta: 'enfermero', pk: 'id_enfermero',
            campos: [
                { id: 'nombre', texto: 'Nombre completo', tipo: 'text', max: 100 },
                { id: 'telefono', texto: 'Teléfono', tipo: 'text', max: 20, patron: /^[0-9+\s-]{7,20}$/, ayuda: 'Solo números, +, espacios y guiones.' },
                { id: 'matricula_profesional', texto: 'Matrícula profesional', tipo: 'text', max: 50 }
            ]
        },
        vehiculos: {
            etiqueta: 'vehículo', pk: 'id_vehiculo',
            campos: [
                { id: 'tipo_vehiculo', texto: 'Tipo', tipo: 'select', opciones: ['Ambulancia', 'Auto'] },
                { id: 'matricula', texto: 'Matrícula', tipo: 'text', max: 20 },
                { id: 'estado', texto: 'Estado', tipo: 'select', opciones: ['Disponible', 'En Traslado', 'Mantenimiento', 'Fuera de Servicio'] }
            ]
        }
    };

    let recurso = 'choferes';
    let filas = [];
    let idEnEdicion = null;

    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) =>
        ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    async function llamar(url, metodo = 'GET', cuerpo = null) {
        const opciones = { method: metodo, headers: {} };
        if (cuerpo !== null) {
            opciones.headers['Content-Type'] = 'application/json';
            opciones.body = JSON.stringify(cuerpo);
        }
        try {
            const res = await fetch(url, opciones);
            return { ok: res.ok, data: await res.json().catch(() => ({})) };
        } catch (e) {
            return { ok: false, data: { mensaje: 'No se pudo conectar con el servidor.' } };
        }
    }
    const mensajeDeError = (r) => (r.data.errores && r.data.errores.length ? r.data.errores.join(' ') : r.data.mensaje || 'Error inesperado.');

    function dibujarFormulario() {
        const cfg = CONFIG[recurso];
        $('#camposForm').innerHTML = cfg.campos.map((c) => {
            const control = c.tipo === 'select'
                ? `<select id="rec_${c.id}">${c.opciones.map((o) => `<option>${esc(o)}</option>`).join('')}</select>`
                : `<input type="text" id="rec_${c.id}" maxlength="${c.max}">`;
            return `<div class="form-group"><label for="rec_${c.id}">${esc(c.texto)} *</label>${control}</div>`;
        }).join('');
        $('#tituloForm').textContent = idEnEdicion === null ? `Nuevo ${cfg.etiqueta}` : `Editando ${cfg.etiqueta} #${idEnEdicion}`;
        $('#btnCancelarRecurso').classList.toggle('oculta', idEnEdicion === null);
        $('#msgRecurso').textContent = '';
    }

    function dibujarTabla() {
        const cfg = CONFIG[recurso];
        $('#tituloLista').textContent = recurso.charAt(0).toUpperCase() + recurso.slice(1);
        $('#encabezadoTabla').innerHTML = '<th>ID</th>' + cfg.campos.map((c) => `<th>${esc(c.texto)}</th>`).join('') + '<th>Acciones</th>';
        if (filas.length === 0) {
            $('#cuerpoTabla').innerHTML = `<tr><td colspan="${cfg.campos.length + 2}" class="text-center text-muted py-4">Sin registros.</td></tr>`;
            return;
        }
        $('#cuerpoTabla').innerHTML = filas.map((f) => `<tr>
            <td>#${esc(f[cfg.pk])}</td>
            ${cfg.campos.map((c) => `<td>${esc(f[c.id])}</td>`).join('')}
            <td>
                <button class="btn-action" data-accion="editar" data-id="${esc(f[cfg.pk])}">Editar</button>
                <button class="btn-action btn-delete" data-accion="eliminar" data-id="${esc(f[cfg.pk])}">Eliminar</button>
            </td></tr>`).join('');
    }

    async function cargar() {
        const r = await llamar(`${API}/${recurso}/index.php`);
        filas = r.ok ? r.data.datos : [];
        dibujarTabla();
    }

    async function cambiarRecurso(nuevo) {
        recurso = nuevo;
        idEnEdicion = null;
        document.querySelectorAll('.tab-recurso').forEach((b) => {
            b.className = 'btn-small tab-recurso ' + (b.dataset.recurso === nuevo ? 'btn-primary' : 'btn-secondary');
        });
        dibujarFormulario();
        await cargar();
    }

    document.querySelectorAll('.tab-recurso').forEach((b) => b.addEventListener('click', () => cambiarRecurso(b.dataset.recurso)));

    $('#formRecurso').addEventListener('submit', async (e) => {
        e.preventDefault();
        const cfg = CONFIG[recurso];
        const datos = {};
        const errores = [];
        cfg.campos.forEach((c) => {
            const valor = $('#rec_' + c.id).value.trim();
            datos[c.id] = valor;
            if (!valor) errores.push(`Complete "${c.texto}".`);
            else if (c.patron && !c.patron.test(valor)) errores.push(`${c.texto}: ${c.ayuda}`);
        });
        if (errores.length) {
            $('#msgRecurso').textContent = errores.join(' ');
            return;
        }

        const r = idEnEdicion === null
            ? await llamar(`${API}/${recurso}/index.php`, 'POST', datos)
            : await llamar(`${API}/${recurso}/index.php?id=${idEnEdicion}`, 'PUT', datos);
        if (!r.ok) {
            $('#msgRecurso').textContent = mensajeDeError(r);
            return;
        }
        idEnEdicion = null;
        dibujarFormulario();
        await cargar();
    });

    $('#btnCancelarRecurso').addEventListener('click', () => { idEnEdicion = null; dibujarFormulario(); });

    $('#cuerpoTabla').addEventListener('click', async (e) => {
        const btn = e.target.closest('button[data-accion]');
        if (!btn) return;
        const cfg = CONFIG[recurso];
        const id = parseInt(btn.dataset.id, 10);

        if (btn.dataset.accion === 'editar') {
            const fila = filas.find((f) => Number(f[cfg.pk]) === id);
            idEnEdicion = id;
            dibujarFormulario();
            cfg.campos.forEach((c) => { $('#rec_' + c.id).value = fila[c.id]; });
        } else if (confirm(`¿Eliminar el ${cfg.etiqueta} #${id}?`)) {
            const r = await llamar(`${API}/${recurso}/index.php?id=${id}`, 'DELETE');
            if (!r.ok) alert(mensajeDeError(r));
            await cargar();
        }
    });

    await cambiarRecurso('choferes');
});

```

---

## Ruta: `frontend\js\ambulancias.js`

```javascript
/**
 * Panel de gestión de traslados (módulo Ambulancias).
 * Consume la API REST (/api/traslados, /api/vehiculos, /api/choferes, /api/enfermeros,
 * /api/pacientes y /api/objetos). Las reglas de negocio las valida el backend (PHP);
 * aquí se hace la validación previa en el navegador para dar respuesta inmediata.
 */
document.addEventListener('DOMContentLoaded', async () => {
    const API = '../../api';
    const ROLES_TRANSPORTE = ['SUPERADMIN_IT', 'GESTOR_INTEGRAL'];

    const usuario = await window.authPromise;
    if (!usuario) return;

    const esTransporte = ROLES_TRANSPORTE.includes(usuario.rol);
    const esDivision = usuario.rol === 'USUARIO_DIVISION';
    const puedeCrear = esTransporte || esDivision;

    // ---------------------------------------------------------------- Selectores
    const $ = (sel) => document.querySelector(sel);
    const form = $('#formAltaTraslado');
    const tabla = $('#tablaTrasladosCuerpo');
    const msgFormulario = $('#msgFormulario');
    const campos = [
        'fecha', 'hora_salida', 'hora_estimada_llegada', 'limite_horario',
        'sector_origen', 'piso_origen', 'habitacion_origen',
        'sector_destino', 'piso_destino', 'habitacion_destino', 'direccion_destino',
        'division_solicitante', 'motivo_procedimiento', 'responsable_recepcion',
        'prioridad', 'observaciones'
    ];

    let traslados = [];
    let catalogos = { vehiculos: [], choferes: [], enfermeros: [], objetos: [] };
    let idEnEdicion = null;

    // ---------------------------------------------------------------- Utilidades
    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) =>
        ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const hhmm = (h) => (h ? h.substring(0, 5) : '--:--');

    async function llamar(url, metodo = 'GET', cuerpo = null) {
        const opciones = { method: metodo, headers: {} };
        if (cuerpo !== null) {
            opciones.headers['Content-Type'] = 'application/json';
            opciones.body = JSON.stringify(cuerpo);
        }
        try {
            const res = await fetch(url, opciones);
            const data = await res.json().catch(() => ({}));
            if (res.status === 401) {
                window.location.href = '../index.html';
            }
            return { ok: res.ok, status: res.status, data };
        } catch (e) {
            return { ok: false, status: 0, data: { mensaje: 'No se pudo conectar con el servidor.' } };
        }
    }

    const mensajeDeError = (r) => {
        const lista = r.data.errores && r.data.errores.length ? r.data.errores : [r.data.mensaje || 'Error inesperado.'];
        return lista.join(' ');
    };

    // ---------------------------------------------------------------- Permisos de interfaz
    if (!puedeCrear) $('#seccionFormulario').classList.add('oculta');
    if (esDivision) $('#grupoRecursos').classList.add('oculta');
    if (esTransporte) {
        $('#lnkRecursos').classList.remove('oculta');
        $('#lnkChofer').classList.remove('oculta');
    }
    if (esDivision) {
        $('#subtituloFormulario').textContent = 'Solicitud de traslado: el área de transporte asignará vehículo y personal.';
        $('#btnGuardar').textContent = 'Enviar Solicitud';
    }

    // ---------------------------------------------------------------- Catálogos
    function llenarSelect(select, filas, textoVacio, etiqueta) {
        select.innerHTML = `<option value="">${esc(textoVacio)}</option>` +
            filas.map((f) => `<option value="${esc(f.id)}">${esc(etiqueta(f))}</option>`).join('');
    }

    async function cargarCatalogos() {
        const [v, c, e, o] = await Promise.all([
            llamar(`${API}/vehiculos/index.php`), llamar(`${API}/choferes/index.php`),
            llamar(`${API}/enfermeros/index.php`), llamar(`${API}/objetos/index.php`)
        ]);
        catalogos.vehiculos = v.data.datos || [];
        catalogos.choferes = c.data.datos || [];
        catalogos.enfermeros = e.data.datos || [];
        catalogos.objetos = o.data.datos || [];

        llenarSelect($('#vehiculo'), catalogos.vehiculos.map((x) => ({ ...x, id: x.id_vehiculo })), 'Sin asignar',
            (x) => `${x.tipo_vehiculo} ${x.matricula} (${x.estado})`);
        llenarSelect($('#filtroVehiculo'), catalogos.vehiculos.map((x) => ({ ...x, id: x.id_vehiculo })), 'Todos los vehículos',
            (x) => `${x.tipo_vehiculo} ${x.matricula}`);
        llenarSelect($('#chofer'), catalogos.choferes.map((x) => ({ ...x, id: x.id_chofer })), 'Sin asignar',
            (x) => x.nombre);
        llenarSelect($('#enfermero'), catalogos.enfermeros.map((x) => ({ ...x, id: x.id_enfermero })), 'Sin asignar / no requiere',
            (x) => x.nombre);
        llenarSelect($('#objeto'), catalogos.objetos.map((x) => ({ ...x, id: x.id_objeto })), 'Seleccione material',
            (x) => x.nombre);
    }

    // ---------------------------------------------------------------- Tipo de carga (paciente / objeto)
    function esOrgano() {
        const obj = catalogos.objetos.find((o) => String(o.id_objeto) === $('#objeto').value);
        return !!obj && obj.tipo_objeto === 'organo';
    }

    function actualizarTipo() {
        const esPaciente = $('#tipo_traslado').value === 'paciente';
        $('#grupo_paciente').classList.toggle('oculta', !esPaciente);
        $('#grupo_objeto').classList.toggle('oculta', esPaciente);
        $('#grupo_enfermero').classList.toggle('oculta', !esPaciente); // RNE03: un objeto no lleva enfermero
        if (!esPaciente) $('#enfermero').value = '';
        actualizarOrgano();
    }

    function actualizarOrgano() {
        const organo = $('#tipo_traslado').value === 'objeto' && esOrgano();
        $('#lblLimite').textContent = organo ? 'Hora límite * (órgano)' : 'Límite de horario';
        if (organo) $('#prioridad').value = 'Alta'; // RNE05
        $('#prioridad').disabled = organo;
    }

    $('#tipo_traslado').addEventListener('change', actualizarTipo);
    $('#objeto').addEventListener('change', actualizarOrgano);

    // Autocompleta los datos si la C.I. ya está registrada
    $('#pac_documento').addEventListener('blur', async () => {
        const doc = $('#pac_documento').value.trim();
        if (!/^[0-9]{6,12}$/.test(doc)) return;
        const r = await llamar(`${API}/pacientes/index.php?documento=${encodeURIComponent(doc)}`);
        const p = r.ok && r.data.datos && r.data.datos[0];
        if (p) {
            cargarPaciente(p);
            $('#pacienteAviso').textContent = 'Paciente ya registrado: se cargaron sus datos (puede actualizarlos).';
        } else {
            $('#pacienteAviso').textContent = 'Paciente nuevo: complete sus datos para registrarlo.';
        }
    });

    function cargarPaciente(p) {
        $('#pac_documento').value = p.documento;
        $('#pac_nombre').value = p.nombre;
        $('#pac_nacimiento').value = p.fecha_de_nacimiento;
        $('#pac_telefono').value = p.telefono || '';
        $('#pac_medios').checked = !!Number(p.se_maneja_por_sus_medios);
        $('#pac_oxigeno').checked = !!Number(p.requiere_oxigeno);
        $('#pac_camilla').checked = !!Number(p.requiere_camilla);
        $('#pac_aislamiento').checked = !!Number(p.requiere_aislamiento);
    }

    // ---------------------------------------------------------------- Validación en el navegador
    function validarFormulario() {
        const errores = [];
        const v = (id) => $('#' + id).value.trim();
        const esPaciente = $('#tipo_traslado').value === 'paciente';

        ['fecha', 'hora_salida', 'hora_estimada_llegada', 'sector_origen', 'piso_origen', 'sector_destino',
            'piso_destino', 'direccion_destino', 'division_solicitante', 'motivo_procedimiento',
            'responsable_recepcion'].forEach((id) => {
            if (!v(id)) errores.push(`Complete el campo "${$('label[for="' + id + '"]').textContent.replace('*', '').trim()}".`);
        });

        if (esPaciente) {
            if (!/^[0-9]{6,12}$/.test(v('pac_documento'))) errores.push('La C.I. debe tener entre 6 y 12 dígitos, sin puntos ni guiones.');
            if (!v('pac_nombre')) errores.push('Ingrese el nombre del paciente.');
            if (!v('pac_nacimiento')) errores.push('Ingrese la fecha de nacimiento del paciente.');
            else if (v('pac_nacimiento') > new Date().toISOString().substring(0, 10)) errores.push('La fecha de nacimiento no puede ser futura.');
            if (v('pac_telefono') && !/^[0-9+\s-]{7,20}$/.test(v('pac_telefono'))) errores.push('El teléfono del paciente no es válido.');
        } else if (!$('#objeto').value) {
            errores.push('Seleccione el órgano o material a trasladar.');
        }

        if (v('hora_salida') && v('hora_estimada_llegada') && v('hora_estimada_llegada') <= v('hora_salida')) {
            errores.push('La llegada estimada debe ser posterior a la hora de salida.');
        }
        if (!esPaciente && esOrgano() && !v('limite_horario')) {
            errores.push('El traslado de un órgano exige la hora límite.');
        }
        if (esPaciente && !esDivision && $('#prioridad').value === 'Alta' && $('#vehiculo').value && !$('#enfermero').value) {
            errores.push('Un paciente con prioridad Alta requiere enfermero acompañante.');
        }
        return errores;
    }

    // ---------------------------------------------------------------- Guardar (alta / edición)
    async function guardarPaciente() {
        const r = await llamar(`${API}/pacientes/index.php`, 'POST', {
            documento: $('#pac_documento').value.trim(),
            nombre: $('#pac_nombre').value.trim(),
            telefono: $('#pac_telefono').value.trim(),
            fecha_de_nacimiento: $('#pac_nacimiento').value,
            se_maneja_por_sus_medios: $('#pac_medios').checked ? 1 : 0,
            requiere_oxigeno: $('#pac_oxigeno').checked ? 1 : 0,
            requiere_camilla: $('#pac_camilla').checked ? 1 : 0,
            requiere_aislamiento: $('#pac_aislamiento').checked ? 1 : 0
        });
        return r;
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        msgFormulario.textContent = '';

        const errores = validarFormulario();
        if (errores.length) {
            msgFormulario.textContent = errores.join(' ');
            return;
        }

        const payload = {};
        campos.forEach((id) => { payload[id] = $('#' + id).value.trim(); });
        payload.prioridad = $('#prioridad').value; // el select puede estar deshabilitado (órgano)
        if (esTransporte) {
            payload.id_vehiculo = $('#vehiculo').value || null;
            payload.id_chofer = $('#chofer').value || null;
            payload.id_enfermero = $('#enfermero').value || null;
        }

        $('#btnGuardar').disabled = true;
        if ($('#tipo_traslado').value === 'paciente') {
            const rp = await guardarPaciente();
            if (!rp.ok) {
                msgFormulario.textContent = mensajeDeError(rp);
                $('#btnGuardar').disabled = false;
                return;
            }
            payload.id_paciente = rp.data.id;
        } else {
            payload.id_objeto = $('#objeto').value;
        }

        const r = idEnEdicion === null
            ? await llamar(`${API}/traslados/index.php`, 'POST', payload)
            : await llamar(`${API}/traslados/index.php?id=${idEnEdicion}`, 'PUT', payload);
        $('#btnGuardar').disabled = false;

        if (!r.ok) {
            msgFormulario.textContent = mensajeDeError(r);
            return;
        }
        alert(idEnEdicion === null ? 'Traslado registrado correctamente.' : 'Traslado actualizado correctamente.');
        salirDeEdicion();
        await cargarTraslados();
    });

    // ---------------------------------------------------------------- Edición
    async function entrarEnEdicion(id) {
        const t = traslados.find((x) => Number(x.id_traslado) === id);
        if (!t) return;
        idEnEdicion = id;
        form.reset();

        $('#tipo_traslado').value = t.id_paciente ? 'paciente' : 'objeto';
        actualizarTipo();
        if (t.id_paciente) {
            const r = await llamar(`${API}/pacientes/index.php?id=${t.id_paciente}`);
            if (r.ok) cargarPaciente(r.data.datos);
        } else {
            $('#objeto').value = String(t.id_objeto);
        }
        campos.forEach((c) => { $('#' + c).value = (t[c] || '').toString().replace(/^(\d{2}:\d{2}):\d{2}$/, '$1'); });
        if (esTransporte) {
            $('#vehiculo').value = t.id_vehiculo || '';
            $('#chofer').value = t.id_chofer || '';
            $('#enfermero').value = t.id_enfermero || '';
        }
        actualizarOrgano();

        $('#tituloFormulario').textContent = `Editando traslado #${id}`;
        $('#btnGuardar').textContent = 'Guardar Cambios';
        $('#btnCancelarEdicion').classList.remove('oculta');
        $('#seccionFormulario').scrollIntoView({ behavior: 'smooth' });
    }

    function salirDeEdicion() {
        idEnEdicion = null;
        form.reset();
        $('#fecha').value = new Date().toISOString().substring(0, 10);
        $('#tituloFormulario').textContent = 'Nuevo Traslado';
        $('#btnGuardar').textContent = esDivision ? 'Enviar Solicitud' : 'Registrar Traslado';
        $('#btnCancelarEdicion').classList.add('oculta');
        $('#pacienteAviso').textContent = 'Si la C.I. ya existe, se cargan sus datos automáticamente.';
        msgFormulario.textContent = '';
        actualizarTipo();
    }
    $('#btnCancelarEdicion').addEventListener('click', salirDeEdicion);

    // ---------------------------------------------------------------- Listado
    function filtrosActuales() {
        const p = new URLSearchParams();
        if ($('#filtroDocumento').value.trim()) p.set('documento', $('#filtroDocumento').value.trim());
        if ($('#filtroVehiculo').value) p.set('id_vehiculo', $('#filtroVehiculo').value);
        if ($('#filtroEstado').value) p.set('estado', $('#filtroEstado').value);
        if ($('#filtroDesde').value) p.set('fecha_desde', $('#filtroDesde').value);
        if ($('#filtroHasta').value) p.set('fecha_hasta', $('#filtroHasta').value);
        return p.toString();
    }

    async function cargarTraslados() {
        const qs = filtrosActuales();
        const r = await llamar(`${API}/traslados/index.php${qs ? '?' + qs : ''}`);
        traslados = r.ok ? (r.data.datos || []) : [];
        actualizarEstadisticas();
        renderizarTabla();
    }

    function actualizarEstadisticas() {
        const cuenta = (...estados) => traslados.filter((t) => estados.includes(t.estado)).length;
        $('#statTotalVal').textContent = traslados.length;
        $('#statPendientesVal').textContent = cuenta('Pendiente');
        $('#statEnCursoVal').textContent = cuenta('En Curso', 'Retornando');
        $('#statCompletadosVal').textContent = cuenta('Finalizado');
    }

    const BADGE_ESTADO = {
        'Pendiente': 'status-yellow', 'En Curso': 'status-blue pulse-badge', 'Retornando': 'status-blue',
        'Finalizado': 'status-green', 'Cancelado': 'status-red'
    };
    const BADGE_PRIORIDAD = { 'Alta': 'badge-prio-alta', 'Media': 'badge-prio-media', 'Baja': 'badge-prio-baja' };

    function botonesDeAccion(t) {
        if (!esTransporte) return '<span class="text-muted" style="font-size:0.8rem;">—</span>';
        const id = t.id_traslado;
        const boton = (accion, texto, clase = '') =>
            `<button class="btn-action ${clase}" data-accion="${accion}" data-id="${id}">${texto}</button>`;
        const acciones = [];
        if (t.estado === 'Pendiente') acciones.push(boton('editar', 'Editar'), boton('En Curso', 'Iniciar'));
        if (t.estado === 'En Curso') acciones.push(boton('Retornando', 'Registrar llegada'));
        if (t.estado === 'Retornando') acciones.push(boton('Finalizado', 'Finalizar'));
        if (['Pendiente', 'En Curso', 'Retornando'].includes(t.estado)) acciones.push(boton('Cancelado', 'Cancelar', 'btn-delete'));
        return acciones.length ? acciones.join(' ') : '<span class="text-muted" style="font-size:0.8rem;">Ninguna</span>';
    }

    function renderizarTabla() {
        if (traslados.length === 0) {
            tabla.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">No hay traslados que coincidan con la búsqueda.</td></tr>';
            return;
        }
        tabla.innerHTML = traslados.map((t) => {
            const carga = t.id_paciente
                ? `${esc(t.paciente_nombre)}<br><small>C.I. ${esc(t.paciente_documento)}</small>`
                : `${esc(t.objeto_nombre)} <small>(Objeto)</small>`;
            const recursos = (t.id_vehiculo || t.id_chofer)
                ? `<strong>Vehículo:</strong> ${esc(t.vehiculo_matricula || 'Sin asignar')}<br>
                   <strong>Chofer:</strong> ${esc(t.chofer_nombre || 'Sin asignar')}<br>
                   <strong>Enf:</strong> ${esc(t.enfermero_nombre || '—')}`
                : '<em>Pendiente de asignación</em>';
            return `<tr>
                <td><strong>#${esc(t.id_traslado)}</strong></td>
                <td>${carga}</td>
                <td><div style="font-size:0.85rem;">
                    <span style="color:#16a34a;">Desde: ${esc(t.sector_origen)} (P. ${esc(t.piso_origen)})</span><br>
                    <span style="color:#dc2626;">Hacia: ${esc(t.sector_destino)} - ${esc(t.direccion_destino)}</span></div></td>
                <td><div style="font-size:0.75rem; color:#475569;">${recursos}</div></td>
                <td><div style="font-size:0.75rem;">${esc(t.fecha)}<br>Sale ${hhmm(t.hora_salida)}<br>
                    Est. ${hhmm(t.hora_estimada_llegada)} / Real ${hhmm(t.hora_efectiva_llegada)}<br>
                    Retorno ${hhmm(t.hora_retorno)}</div></td>
                <td><span class="${BADGE_PRIORIDAD[t.prioridad] || ''}">${esc(t.prioridad)}</span></td>
                <td><span class="status-badge ${BADGE_ESTADO[t.estado] || ''}">${esc(t.estado)}</span></td>
                <td>${botonesDeAccion(t)}</td>
            </tr>`;
        }).join('');
    }

    // Acciones sobre una fila: editar o cambiar de estado / cancelar
    tabla.addEventListener('click', async (e) => {
        const btn = e.target.closest('button[data-accion]');
        if (!btn) return;
        const id = parseInt(btn.dataset.id, 10);
        const accion = btn.dataset.accion;

        if (accion === 'editar') {
            await entrarEnEdicion(id);
            return;
        }
        if (accion === 'Cancelado') {
            if (!confirm(`¿Confirma la cancelación del traslado #${id}?`)) return;
            const r = await llamar(`${API}/traslados/index.php?id=${id}`, 'DELETE');
            if (!r.ok) alert(mensajeDeError(r));
        } else {
            const r = await llamar(`${API}/traslados/index.php?id=${id}&accion=estado`, 'PUT', { estado: accion });
            if (!r.ok) alert(mensajeDeError(r));
        }
        await cargarTraslados();
    });

    $('#formFiltros').addEventListener('submit', (e) => { e.preventDefault(); cargarTraslados(); });
    $('#btnLimpiarFiltros').addEventListener('click', () => {
        $('#formFiltros').reset();
        cargarTraslados();
    });

    // ---------------------------------------------------------------- Inicio
    await cargarCatalogos();
    salirDeEdicion();
    await cargarTraslados();
    setInterval(() => { if (idEnEdicion === null) cargarTraslados(); }, 30000); // seguimiento casi en tiempo real
});

```

---

## Ruta: `frontend\js\auth.js`

```javascript

window.authPromise = (async () => {
    try {
        const res = await fetch('../../api/sesion.php');
        const data = await res.json();

        if (!res.ok || !data.autenticado) {
            window.location.href = '../index.html';
            return null;
        }

        // Guardar en el objeto global
        window.currentUser = data.usuario;

        // Intentar actualizar elementos comunes del header si existen en el DOM
        const txtNombre = document.querySelector('#txtNombre');
        const txtRol = document.querySelector('#txtRol');
        const btnCerrarSesion = document.querySelector('#btnCerrarSesion');

        if (txtNombre) {
            if (txtRol) {
                txtNombre.textContent = data.usuario.nombre;
                txtRol.textContent = data.usuario.rol;
            } else {
                txtNombre.textContent = `${data.usuario.nombre} (${data.usuario.rol})`;
            }
        }

        if (btnCerrarSesion) {
            btnCerrarSesion.addEventListener('click', async () => {
                try {
                    await fetch('../../api/logout.php', { method: 'POST' });
                    window.location.href = '../index.html';
                } catch (err) {
                    alert('Error al cerrar sesión.');
                }
            });
        }

        return data.usuario;
    } catch (error) {
        console.error('Error de autenticación:', error);
        window.location.href = '../index.html';
        return null;
    }
})();

```

---

## Ruta: `frontend\js\documentos.js`

```javascript
/**
 * Módulo de Documentación Clínica, Categorías y Resultados de Encuestas de Satisfacción.
 * Consume los endpoints REST con métodos HTTP adecuados:
 *   - Documentos: GET /api/documentos, POST /api/documentos, PUT /api/documentos/{id}, DELETE /api/documentos/{id}
 *   - Categorías: GET /api/categorias, POST /api/categorias, PUT /api/categorias/{id}, DELETE /api/categorias/{id}
 *   - Encuestas:  GET /api/encuestas/resultados
 */
document.addEventListener('DOMContentLoaded', async () => {
    const API = '../../api';
    const TAMANO_MAXIMO = 10 * 1024 * 1024; // 10 MB (igual que en Ejemplo2 de clase)

    const usuario = await window.authPromise;
    if (!usuario) return;

    const puedeEscribirDocs = ['SUPERADMIN_IT', 'GESTOR_INTEGRAL', 'USUARIO_DIVISION'].includes(usuario.rol);
    const puedeGestionarCategorias = ['SUPERADMIN_IT', 'GESTOR_INTEGRAL'].includes(usuario.rol);

    // ---------------------------------------------------------------- Selectores
    const $ = (sel) => document.querySelector(sel);
    const cajaAlerta = $('#alerta');

    const inputBuscar = $('#inputBuscar');
    const selectFiltroCategoria = $('#selectFiltroCategoria');
    const tablaCuerpo = $('#tablaCuerpo');

    const btnAbrirModalAlta = $('#btnAbrirModalAlta');
    const modalAlta = $('#modalAlta');
    const formAltaDocumento = $('#formAltaDocumento');
    const selectCategoriaForm = $('#id_categoria');
    const btnGuardarDocumento = $('#btnGuardarDocumento');

    const modalEditar = $('#modalEditar');
    const formEditarDocumento = $('#formEditarDocumento');
    const selectCategoriaEditForm = $('#edit_id_categoria');
    const btnActualizarDocumento = $('#btnActualizarDocumento');

    // Categorías CRUD
    const formCategoria = $('#formCategoria');
    const inputCatId = $('#cat_id');
    const inputCatNombre = $('#cat_nombre');
    const btnGuardarCategoria = $('#btnGuardarCategoria');
    const btnCancelarCategoria = $('#btnCancelarCategoria');
    const tablaCategoriasCuerpo = $('#tablaCategoriasCuerpo');

    // Encuestas
    const selectFiltroEncuestaCategoria = $('#selectFiltroEncuestaCategoria');
    const btnRefrescarEncuestas = $('#btnRefrescarEncuestas');
    const btnAbrirModalNuevaEncuesta = $('#btnAbrirModalNuevaEncuesta');
    const contenedorResultadosEncuestas = $('#contenedorResultadosEncuestas');

    const modalEncuestaAdmin = $('#modalEncuestaAdmin');
    const formEncuestaAdmin = $('#formEncuestaAdmin');
    const selectEncAdminCategoria = $('#enc_admin_categoria');

    const modalPreguntaAdmin = $('#modalPreguntaAdmin');
    const formPreguntaAdmin = $('#formPreguntaAdmin');
    const selectPregAdminTipo = $('#preg_admin_tipo');
    const grupoPregAdminOpciones = $('#grupoPregAdminOpciones');

    let documentosList = [];
    let categoriasList = [];
    let encuestasList = [];

    if (!puedeEscribirDocs && btnAbrirModalAlta) {
        btnAbrirModalAlta.classList.add('seccion-oculta');
    }
    if (!puedeGestionarCategorias && formCategoria) {
        formCategoria.classList.add('seccion-oculta');
    }
    if (!puedeGestionarCategorias && btnAbrirModalNuevaEncuesta) {
        btnAbrirModalNuevaEncuesta.classList.add('seccion-oculta');
    }

    // ---------------------------------------------------------------- Utilidades
    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) =>
        ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    const mostrarAlerta = (mensaje, tipo = 'error') => {
        cajaAlerta.textContent = mensaje;
        cajaAlerta.classList.remove('oculta', 'error', 'exito');
        cajaAlerta.classList.add(tipo);
        setTimeout(() => cajaAlerta.classList.add('oculta'), 5000);
    };

    const validarArchivoCliente = (archivo) => {
        if (archivo.type !== 'application/pdf' && !archivo.name.toLowerCase().endsWith('.pdf')) {
            return 'Formato no permitido. Seleccione únicamente un archivo PDF.';
        }
        if (archivo.size > TAMANO_MAXIMO) {
            return 'El archivo no puede superar los 10 MB.';
        }
        return null;
    };

    // ---------------------------------------------------------------- Pestañas del módulo
    document.querySelectorAll('.tab-modulo-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.tab-modulo-btn').forEach((b) => b.classList.remove('active'));
            btn.classList.add('active');

            const vista = btn.dataset.vista;
            $('#vistaDocumentos').classList.toggle('seccion-oculta', vista !== 'documentos');
            $('#vistaCategorias').classList.toggle('seccion-oculta', vista !== 'categorias');
            $('#vistaEncuestas').classList.toggle('seccion-oculta', vista !== 'encuestas');

            if (vista === 'encuestas') {
                cargarResultadosEncuestas();
            }
        });
    });

    // ---------------------------------------------------------------- Categorías (CRUD)
    const poblarSelectoresCategorias = () => {
        const valorFiltroActual = selectFiltroCategoria.value || '0';
        const valorFiltroEncActual = selectFiltroEncuestaCategoria.value || '0';

        selectFiltroCategoria.innerHTML = '<option value="0">Todas las categorías</option>';
        selectFiltroEncuestaCategoria.innerHTML = '<option value="0">Todas las categorías con encuesta</option>';
        selectCategoriaForm.innerHTML = '<option value="">Seleccione una categoría</option>';
        selectCategoriaEditForm.innerHTML = '<option value="">Seleccione una categoría</option>';

        categoriasList.forEach((cat) => {
            const crearOpt = () => {
                const o = document.createElement('option');
                o.value = cat.id_categoria;
                o.textContent = cat.nombre;
                return o;
            };
            selectFiltroCategoria.appendChild(crearOpt());
            selectFiltroEncuestaCategoria.appendChild(crearOpt());
            selectCategoriaForm.appendChild(crearOpt());
            selectCategoriaEditForm.appendChild(crearOpt());
            if (selectEncAdminCategoria) selectEncAdminCategoria.appendChild(crearOpt());
        });

        selectFiltroCategoria.value = valorFiltroActual;
        selectFiltroEncuestaCategoria.value = valorFiltroEncActual;
    };

    const renderizarTablaCategorias = () => {
        if (categoriasList.length === 0) {
            tablaCategoriasCuerpo.innerHTML = '<tr><td colspan="3" class="text-center">No hay categorías registradas.</td></tr>';
            return;
        }
        tablaCategoriasCuerpo.innerHTML = categoriasList.map((cat) => {
            const acciones = puedeGestionarCategorias
                ? `<button type="button" class="btn-action btn-edit-cat" data-id="${esc(cat.id_categoria)}">Editar</button>
                   <button type="button" class="btn-action btn-delete btn-delete-cat" data-id="${esc(cat.id_categoria)}">Eliminar</button>`
                : '<span style="color:#64748b; font-size:0.8rem;">Solo lectura</span>';
            return `<tr>
                <td>#${esc(cat.id_categoria)}</td>
                <td><strong>${esc(cat.nombre)}</strong></td>
                <td>${acciones}</td>
            </tr>`;
        }).join('');
    };

    const cargarCategorias = async () => {
        try {
            const res = await fetch(`${API}/categorias/index.php`, { method: 'GET' });
            const data = await res.json();
            if (res.ok && data.status === 'ok') {
                categoriasList = data.datos || [];
                poblarSelectoresCategorias();
                renderizarTablaCategorias();
            }
        } catch (error) {
            mostrarAlerta('Error al obtener el listado de categorías.', 'error');
        }
    };

    const salirEdicionCategoria = () => {
        inputCatId.value = '';
        inputCatNombre.value = '';
        btnGuardarCategoria.textContent = 'Crear Categoría';
        btnCancelarCategoria.classList.add('seccion-oculta');
    };

    if (formCategoria) {
        formCategoria.addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = inputCatId.value.trim();
            const nombre = inputCatNombre.value.trim();

            if (!nombre) {
                mostrarAlerta('Ingrese el nombre de la categoría.', 'error');
                return;
            }

            const url = id ? `${API}/categorias/index.php?id=${id}` : `${API}/categorias/index.php`;
            const metodo = id ? 'PUT' : 'POST';

            try {
                const res = await fetch(url, {
                    method: metodo,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ nombre })
                });
                const data = await res.json();
                if (!res.ok) {
                    throw new Error(data.mensaje || 'No se pudo guardar la categoría.');
                }
                mostrarAlerta(data.mensaje, 'exito');
                salirEdicionCategoria();
                await cargarCategorias();
                await obtenerDocumentos();
            } catch (err) {
                mostrarAlerta(err.message, 'error');
            }
        });
    }

    if (btnCancelarCategoria) {
        btnCancelarCategoria.addEventListener('click', salirEdicionCategoria);
    }

    tablaCategoriasCuerpo.addEventListener('click', async (e) => {
        const btnEdit = e.target.closest('.btn-edit-cat');
        if (btnEdit) {
            const id = parseInt(btnEdit.dataset.id, 10);
            const cat = categoriasList.find((c) => parseInt(c.id_categoria, 10) === id);
            if (!cat) return;
            inputCatId.value = cat.id_categoria;
            inputCatNombre.value = cat.nombre;
            btnGuardarCategoria.textContent = 'Guardar Cambios';
            btnCancelarCategoria.classList.remove('seccion-oculta');
            inputCatNombre.focus();
            return;
        }

        const btnDel = e.target.closest('.btn-delete-cat');
        if (btnDel) {
            const id = parseInt(btnDel.dataset.id, 10);
            const cat = categoriasList.find((c) => parseInt(c.id_categoria, 10) === id);
            if (!confirm(`¿Confirma la eliminación de la categoría "${cat ? cat.nombre : '#' + id}"?`)) return;

            try {
                const res = await fetch(`${API}/categorias/index.php?id=${id}`, { method: 'DELETE' });
                const data = await res.json();
                if (!res.ok) {
                    throw new Error(data.mensaje || 'No se pudo eliminar la categoría.');
                }
                mostrarAlerta(data.mensaje, 'exito');
                if (inputCatId.value === String(id)) salirEdicionCategoria();
                await cargarCategorias();
            } catch (err) {
                mostrarAlerta(err.message, 'error');
            }
        }
    });

    // ---------------------------------------------------------------- Documentos (CRUD)
    const renderizarTabla = () => {
        const busqueda = inputBuscar.value.toLowerCase().trim();
        const categoriaId = parseInt(selectFiltroCategoria.value, 10);

        const docsFiltrados = documentosList.filter((doc) => {
            const coincideTitulo = doc.titulo.toLowerCase().includes(busqueda);
            const coincideCategoria = categoriaId === 0 || parseInt(doc.id_categoria, 10) === categoriaId;
            return coincideTitulo && coincideCategoria;
        });

        tablaCuerpo.innerHTML = '';

        if (docsFiltrados.length === 0) {
            tablaCuerpo.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center">No se encontraron documentos con los criterios ingresados.</td>
                </tr>
            `;
            return;
        }

        docsFiltrados.forEach((doc) => {
            const tr = document.createElement('tr');
            const acciones = puedeEscribirDocs
                ? `<button type="button" class="btn-action btn-edit" data-id="${esc(doc.id_documento)}">Editar</button>
                   <button type="button" class="btn-action btn-delete" data-id="${esc(doc.id_documento)}">Eliminar</button>`
                : '<span style="color:#64748b; font-size:0.8rem;">Solo lectura</span>';

            tr.innerHTML = `
                <td>#${esc(doc.id_documento)}</td>
                <td><strong>${esc(doc.titulo)}</strong></td>
                <td>${esc(doc.categoria_nombre)}</td>
                <td>${esc(doc.autor_nombre || '—')}</td>
                <td>
                    <a href="../../${esc(doc.archivo)}" target="_blank" class="btn-action">Ver Archivo</a>
                </td>
                <td>${acciones}</td>
            `;
            tablaCuerpo.appendChild(tr);
        });
    };

    const obtenerDocumentos = async () => {
        try {
            const res = await fetch(`${API}/documentos/index.php`, { method: 'GET' });
            const data = await res.json();

            if (res.ok && data.status === 'ok') {
                documentosList = data.datos || [];
                renderizarTabla();
            } else {
                throw new Error(data.mensaje || 'Error al obtener documentos.');
            }
        } catch (error) {
            mostrarAlerta(error.message, 'error');
            tablaCuerpo.innerHTML = '<tr><td colspan="6" class="text-center">Error al cargar datos.</td></tr>';
        }
    };

    // Alta de documento: POST /api/documentos (multipart/form-data)
    formAltaDocumento.addEventListener('submit', async (e) => {
        e.preventDefault();

        const titulo = $('#titulo').value.trim();
        const categoria = selectCategoriaForm.value;
        const inputArchivo = $('#archivo');

        if (!titulo || !categoria || inputArchivo.files.length === 0) {
            alert('Por favor complete todos los campos obligatorios y adjunte un archivo.');
            return;
        }

        const errorArchivo = validarArchivoCliente(inputArchivo.files[0]);
        if (errorArchivo) {
            alert(errorArchivo);
            return;
        }

        const formData = new FormData(formAltaDocumento);

        btnGuardarDocumento.disabled = true;
        btnGuardarDocumento.textContent = 'Guardando...';

        try {
            const res = await fetch(`${API}/documentos/index.php`, {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (!res.ok) {
                throw new Error(data.mensaje || 'Error al subir el documento.');
            }

            mostrarAlerta('Documento publicado con éxito.', 'exito');
            formAltaDocumento.reset();
            modalAlta.classList.add('oculta');
            await obtenerDocumentos();
        } catch (error) {
            alert(error.message);
        } finally {
            btnGuardarDocumento.disabled = false;
            btnGuardarDocumento.textContent = 'Guardar Documento';
        }
    });

    // Edición de documento: PUT /api/documentos/{id} con JSON (o multipart si se reemplaza el archivo adjunto)
    formEditarDocumento.addEventListener('submit', async (e) => {
        e.preventDefault();

        const id = $('#edit_id_documento').value;
        const titulo = $('#edit_titulo').value.trim();
        const categoria = selectCategoriaEditForm.value;
        const inputArchivoEdit = $('#edit_archivo');

        if (!id || !titulo || !categoria) {
            alert('Complete todos los campos obligatorios.');
            return;
        }

        const hayNuevoArchivo = inputArchivoEdit.files && inputArchivoEdit.files.length > 0;
        if (hayNuevoArchivo) {
            const errorArchivo = validarArchivoCliente(inputArchivoEdit.files[0]);
            if (errorArchivo) {
                alert(errorArchivo);
                return;
            }
        }

        btnActualizarDocumento.disabled = true;
        btnActualizarDocumento.textContent = 'Actualizando...';

        try {
            let res;
            if (hayNuevoArchivo) {
                const formData = new FormData(formEditarDocumento);
                formData.append('_method', 'PUT');
                res = await fetch(`${API}/documentos/index.php?id=${id}`, {
                    method: 'POST',
                    body: formData
                });
            } else {
                res = await fetch(`${API}/documentos/index.php?id=${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        titulo,
                        id_categoria: parseInt(categoria, 10)
                    })
                });
            }

            const data = await res.json();
            if (!res.ok) {
                throw new Error(data.mensaje || 'Error al actualizar el documento.');
            }

            mostrarAlerta('Documento actualizado correctamente.', 'exito');
            formEditarDocumento.reset();
            modalEditar.classList.add('oculta');
            await obtenerDocumentos();
        } catch (error) {
            alert(error.message);
        } finally {
            btnActualizarDocumento.disabled = false;
            btnActualizarDocumento.textContent = 'Guardar Cambios';
        }
    });

    // Eliminar (DELETE /api/documentos/{id}) o abrir modal de edición
    tablaCuerpo.addEventListener('click', async (e) => {
        if (e.target.classList.contains('btn-delete')) {
            const id = e.target.dataset.id;
            if (!confirm(`¿Confirma la eliminación del documento con ID #${id}?`)) return;

            try {
                const res = await fetch(`${API}/documentos/index.php?id=${id}`, {
                    method: 'DELETE'
                });
                const data = await res.json();

                if (!res.ok) {
                    throw new Error(data.mensaje || 'No se pudo eliminar el documento.');
                }

                mostrarAlerta('Documento eliminado correctamente.', 'exito');
                await obtenerDocumentos();
            } catch (error) {
                mostrarAlerta(error.message, 'error');
            }
        }

        if (e.target.classList.contains('btn-edit')) {
            const id = parseInt(e.target.dataset.id, 10);
            const doc = documentosList.find((d) => parseInt(d.id_documento, 10) === id);
            if (!doc) return;

            $('#edit_id_documento').value = doc.id_documento;
            $('#edit_titulo').value = doc.titulo;
            $('#edit_id_categoria').value = doc.id_categoria;
            $('#edit_archivo').value = '';

            modalEditar.classList.remove('oculta');
        }
    });

    // ---------------------------------------------------------------- Resultados y Gestión de Encuestas
    const renderizarPreguntaResultado = (p, idEncuesta) => {
        const accionesPregunta = puedeGestionarCategorias
            ? `<div style="display:flex; gap:0.35rem; flex-shrink:0;">
                   <button type="button" class="btn-action btn-edit-preg" data-id="${esc(p.id_pregunta)}" data-encuesta="${esc(idEncuesta)}">Editar</button>
                   <button type="button" class="btn-action btn-delete btn-delete-preg" data-id="${esc(p.id_pregunta)}">Eliminar</button>
               </div>`
            : '';

        const cabecera = `
            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:0.75rem;">
                <div>
                    <strong>${esc(p.enunciado)}</strong>
                    <span class="badge" style="background:#f1f5f9; color:#475569; margin-left:0.35rem;">${esc(p.tipo)}</span>
                </div>
                ${accionesPregunta}
            </div>
        `;

        if (p.tipo === 'Si/No') {
            const total = (p.si || 0) + (p.no || 0);
            const porcSi = total > 0 ? Math.round((p.si / total) * 100) : 0;
            const porcNo = total > 0 ? 100 - porcSi : 0;
            return `
                <div class="pregunta-resultado">
                    ${cabecera}
                    <div style="font-size:0.85rem; color:#475569; margin-top:0.25rem;">
                        Sí: <strong>${p.si}</strong> (${porcSi}%) &nbsp;|&nbsp;
                        No: <strong>${p.no}</strong> (${porcNo}%) &nbsp;|&nbsp;
                        Total: ${total}
                    </div>
                    <div class="barra-progreso"><div style="width:${porcSi}%;"></div></div>
                </div>
            `;
        }

        if (p.tipo === 'Escala_1_5') {
            const prom = p.promedio !== null ? `${p.promedio} / 5` : 'Sin calificaciones';
            const porc = p.promedio !== null ? Math.round((p.promedio / 5) * 100) : 0;
            const dist = p.distribucion || {};
            const detalle = [1, 2, 3, 4, 5].map((n) => `${n}★: ${dist[n] || 0}`).join(' · ');
            return `
                <div class="pregunta-resultado">
                    ${cabecera}
                    <div style="font-size:0.85rem; color:#475569; margin-top:0.25rem;">
                        Promedio: <strong>${esc(prom)}</strong> (${p.total_respuestas} respuestas) — <small>${esc(detalle)}</small>
                    </div>
                    <div class="barra-progreso"><div style="width:${porc}%;"></div></div>
                </div>
            `;
        }

        if (p.tipo === 'Opcion_Multiple') {
            const conteo = p.conteo_opciones || {};
            const detalleOps = Object.entries(conteo).map(([op, cant]) => `${esc(op)}: <strong>${cant}</strong>`).join(' &nbsp;|&nbsp; ');
            return `
                <div class="pregunta-resultado">
                    ${cabecera}
                    <div style="font-size:0.85rem; color:#475569; margin-top:0.25rem;">
                        ${detalleOps || 'Sin opciones registradas'}
                    </div>
                </div>
            `;
        }

        const textos = (p.respuestas || []);
        const items = textos.length > 0
            ? `<ul class="lista-textos">${textos.slice(0, 10).map((t) => `<li>${esc(t)}</li>`).join('')}</ul>`
            : '<p style="font-size:0.85rem; color:#64748b; margin-top:0.25rem;">Sin comentarios registrados.</p>';
        return `
            <div class="pregunta-resultado">
                ${cabecera}
                ${items}
            </div>
        `;
    };

    const cargarResultadosEncuestas = async (preservarScroll = true) => {
        const scrollPrevio = window.scrollY;
        if (!preservarScroll || !contenedorResultadosEncuestas.children.length) {
            contenedorResultadosEncuestas.innerHTML = '<p class="text-center">Consultando resultados...</p>';
        }
        const idCat = parseInt(selectFiltroEncuestaCategoria.value, 10);
        const qs = idCat > 0 ? `&id_categoria=${idCat}` : '';

        try {
            const res = await fetch(`${API}/encuestas/index.php?resultados=1${qs}`, { method: 'GET' });
            const data = await res.json();

            if (!res.ok || data.status !== 'ok') {
                throw new Error(data.mensaje || 'No se pudieron obtener los resultados.');
            }

            encuestasList = data.datos || [];
            if (encuestasList.length === 0) {
                contenedorResultadosEncuestas.innerHTML = '<p class="text-center">No hay encuestas registradas para esta categoría.</p>';
                return;
            }

            contenedorResultadosEncuestas.innerHTML = encuestasList.map((enc) => {
                const preguntasHTML = (enc.preguntas || []).length > 0
                    ? enc.preguntas.map((p) => renderizarPreguntaResultado(p, enc.id_encuesta)).join('')
                    : '<p style="font-size:0.85rem; color:#64748b;">Esta encuesta aún no tiene preguntas cargadas.</p>';
                const obs = enc.observaciones || [];
                const obsHTML = obs.length > 0
                    ? `<div class="pregunta-resultado">
                           <strong>Observaciones generales de los pacientes (${obs.length}):</strong>
                           <ul class="lista-textos">
                               ${obs.slice(0, 10).map((o) => `<li><small style="color:#64748b;">[${esc(o.fecha_hora)}]</small> ${esc(o.texto)}</li>`).join('')}
                           </ul>
                       </div>`
                    : '';

                const botonesEncuesta = puedeGestionarCategorias
                    ? `<div style="margin-top:0.5rem; display:flex; gap:0.35rem; justify-content:flex-end; flex-wrap:wrap;">
                           <button type="button" class="btn-action btn-add-preg" data-encuesta="${esc(enc.id_encuesta)}">+ Agregar Pregunta</button>
                           <button type="button" class="btn-action btn-edit-enc" data-encuesta="${esc(enc.id_encuesta)}">Editar Encuesta</button>
                           <button type="button" class="btn-action btn-delete btn-delete-enc" data-encuesta="${esc(enc.id_encuesta)}">Eliminar Encuesta</button>
                       </div>`
                    : '';

                const colorEstado = enc.estado === 'Activa'
                    ? 'background:#dcfce7; color:#166534;'
                    : (enc.estado === 'Borrador' ? 'background:#fef3c7; color:#92400e;' : 'background:#fee2e2; color:#991b1b;');

                return `
                    <article class="encuesta-card" id="encuesta-card-${esc(enc.id_encuesta)}">
                        <div class="encuesta-header">
                            <div>
                                <span class="badge badge-publico">${esc(enc.categoria_nombre)}</span>
                                <h3 style="margin-top:0.35rem;">${esc(enc.comentarios || 'Encuesta de Satisfacción')}</h3>
                            </div>
                            <div style="text-align:right;">
                                <span class="badge" style="${colorEstado}">Estado: ${esc(enc.estado)}</span>
                                <div style="font-size:0.85rem; color:#475569; margin-top:0.25rem;">
                                    Encuestas respondidas: <strong>${esc(enc.total_resoluciones)}</strong>
                                </div>
                                ${botonesEncuesta}
                            </div>
                        </div>
                        ${preguntasHTML}
                        ${obsHTML}
                    </article>
                `;
            }).join('');

            if (preservarScroll) {
                window.scrollTo({ top: scrollPrevio, behavior: 'instant' });
            }
        } catch (err) {
            contenedorResultadosEncuestas.innerHTML = `<p class="text-center" style="color:#b91c1c;">${esc(err.message)}</p>`;
        }
    };

    selectFiltroEncuestaCategoria.addEventListener('change', () => cargarResultadosEncuestas(false));
    btnRefrescarEncuestas.addEventListener('click', () => cargarResultadosEncuestas(true));

    // Modales de administración de encuestas y preguntas
    if (btnAbrirModalNuevaEncuesta) {
        btnAbrirModalNuevaEncuesta.addEventListener('click', () => {
            formEncuestaAdmin.reset();
            $('#enc_admin_id').value = '';
            $('#tituloModalEncuestaAdmin').textContent = 'Nueva Encuesta por Categoría';
            $('#grupoEncAdminCategoria').classList.remove('seccion-oculta');
            modalEncuestaAdmin.classList.remove('oculta');
        });
    }
    $('#btnCerrarModalEncuestaAdmin').addEventListener('click', () => modalEncuestaAdmin.classList.add('oculta'));
    $('#btnCancelarModalEncuestaAdmin').addEventListener('click', () => modalEncuestaAdmin.classList.add('oculta'));

    formEncuestaAdmin.addEventListener('submit', async (e) => {
        e.preventDefault();
        const idEnc = $('#enc_admin_id').value.trim();
        const id_categoria = parseInt(selectEncAdminCategoria.value, 10);
        const comentarios = $('#enc_admin_comentarios').value.trim();
        const estado = $('#enc_admin_estado').value;

        if (!comentarios || (!idEnc && !id_categoria)) {
            alert('Complete el título y la categoría de la encuesta.');
            return;
        }

        try {
            const url = idEnc ? `${API}/encuestas/index.php?id=${idEnc}` : `${API}/encuestas/index.php?recurso=encuesta`;
            const metodo = idEnc ? 'PUT' : 'POST';
            const res = await fetch(url, {
                method: metodo,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_categoria, comentarios, estado })
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.mensaje || 'Error al guardar la encuesta.');

            mostrarAlerta(data.mensaje, 'exito');
            modalEncuestaAdmin.classList.add('oculta');
            await cargarResultadosEncuestas();
        } catch (err) {
            alert(err.message);
        }
    });

    selectPregAdminTipo.addEventListener('change', () => {
        grupoPregAdminOpciones.classList.toggle('seccion-oculta', selectPregAdminTipo.value !== 'Opcion_Multiple');
    });
    $('#btnCerrarModalPreguntaAdmin').addEventListener('click', () => modalPreguntaAdmin.classList.add('oculta'));
    $('#btnCancelarModalPreguntaAdmin').addEventListener('click', () => modalPreguntaAdmin.classList.add('oculta'));

    formPreguntaAdmin.addEventListener('submit', async (e) => {
        e.preventDefault();
        const idPreg = $('#preg_admin_id').value.trim();
        const id_encuesta = parseInt($('#preg_admin_id_encuesta').value, 10);
        const enunciado = $('#preg_admin_enunciado').value.trim();
        const tipo = selectPregAdminTipo.value;
        const opciones = $('#preg_admin_opciones').value
            .split('\n')
            .map((s) => s.trim())
            .filter((s) => s !== '');

        if (!enunciado) {
            alert('Ingrese el enunciado de la pregunta.');
            return;
        }
        if (tipo === 'Opcion_Multiple' && opciones.length < 2) {
            alert('Ingrese al menos 2 opciones (una por línea).');
            return;
        }

        try {
            const url = idPreg
                ? `${API}/encuestas/index.php?id=${idPreg}&recurso=pregunta`
                : `${API}/encuestas/index.php?recurso=pregunta`;
            const metodo = idPreg ? 'PUT' : 'POST';
            const res = await fetch(url, {
                method: metodo,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_encuesta, enunciado, tipo, opciones, recurso: 'pregunta' })
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.mensaje || 'Error al guardar la pregunta.');

            mostrarAlerta(data.mensaje, 'exito');
            modalPreguntaAdmin.classList.add('oculta');
            await cargarResultadosEncuestas();
        } catch (err) {
            alert(err.message);
        }
    });

    contenedorResultadosEncuestas.addEventListener('click', async (e) => {
        const btnEditEnc = e.target.closest('.btn-edit-enc');
        if (btnEditEnc) {
            const idEnc = parseInt(btnEditEnc.dataset.encuesta, 10);
            const enc = encuestasList.find((x) => parseInt(x.id_encuesta, 10) === idEnc);
            if (!enc) return;
            $('#enc_admin_id').value = enc.id_encuesta;
            selectEncAdminCategoria.value = enc.id_categoria;
            $('#enc_admin_comentarios').value = enc.comentarios || '';
            $('#enc_admin_estado').value = enc.estado;
            $('#tituloModalEncuestaAdmin').textContent = `Editar Encuesta #${enc.id_encuesta}`;
            $('#grupoEncAdminCategoria').classList.add('seccion-oculta');
            modalEncuestaAdmin.classList.remove('oculta');
            return;
        }

        const btnAddPreg = e.target.closest('.btn-add-preg');
        if (btnAddPreg) {
            formPreguntaAdmin.reset();
            $('#preg_admin_id').value = '';
            $('#preg_admin_id_encuesta').value = btnAddPreg.dataset.encuesta;
            $('#tituloModalPreguntaAdmin').textContent = 'Agregar Pregunta a la Encuesta';
            grupoPregAdminOpciones.classList.add('seccion-oculta');
            modalPreguntaAdmin.classList.remove('oculta');
            return;
        }

        const btnEditPreg = e.target.closest('.btn-edit-preg');
        if (btnEditPreg) {
            const idPreg = parseInt(btnEditPreg.dataset.id, 10);
            const idEnc = parseInt(btnEditPreg.dataset.encuesta, 10);
            const enc = encuestasList.find((x) => parseInt(x.id_encuesta, 10) === idEnc);
            const preg = enc && (enc.preguntas || []).find((p) => parseInt(p.id_pregunta, 10) === idPreg);
            if (!preg) return;

            $('#preg_admin_id').value = preg.id_pregunta;
            $('#preg_admin_id_encuesta').value = idEnc;
            $('#preg_admin_enunciado').value = preg.enunciado;
            selectPregAdminTipo.value = preg.tipo;
            $('#preg_admin_opciones').value = (preg.opciones || []).join('\n');
            grupoPregAdminOpciones.classList.toggle('seccion-oculta', preg.tipo !== 'Opcion_Multiple');
            $('#tituloModalPreguntaAdmin').textContent = `Editar Pregunta #${preg.id_pregunta}`;
            modalPreguntaAdmin.classList.remove('oculta');
            return;
        }

        const btnDelPreg = e.target.closest('.btn-delete-preg');
        if (btnDelPreg) {
            const idPreg = parseInt(btnDelPreg.dataset.id, 10);
            if (!confirm(`¿Confirma la eliminación de la pregunta #${idPreg}?`)) return;
            try {
                const res = await fetch(`${API}/encuestas/index.php?id=${idPreg}&recurso=pregunta`, { method: 'DELETE' });
                const data = await res.json();
                if (!res.ok) throw new Error(data.mensaje || 'No se pudo eliminar la pregunta.');
                mostrarAlerta(data.mensaje, 'exito');
                await cargarResultadosEncuestas();
            } catch (err) {
                mostrarAlerta(err.message, 'error');
            }
            return;
        }

        const btnDelEnc = e.target.closest('.btn-delete-enc');
        if (btnDelEnc) {
            const idEnc = parseInt(btnDelEnc.dataset.encuesta, 10);
            if (!confirm(`¿Confirma la eliminación completa de la encuesta #${idEnc} y sus preguntas?`)) return;
            try {
                const res = await fetch(`${API}/encuestas/index.php?id=${idEnc}`, { method: 'DELETE' });
                const data = await res.json();
                if (!res.ok) throw new Error(data.mensaje || 'No se pudo eliminar la encuesta.');
                mostrarAlerta(data.mensaje, 'exito');
                await cargarResultadosEncuestas();
            } catch (err) {
                mostrarAlerta(err.message, 'error');
            }
        }
    });

    // ---------------------------------------------------------------- Eventos de modales y filtros
    inputBuscar.addEventListener('input', renderizarTabla);
    selectFiltroCategoria.addEventListener('change', renderizarTabla);

    btnAbrirModalAlta.addEventListener('click', () => modalAlta.classList.remove('oculta'));
    $('#btnCerrarModalAlta').addEventListener('click', () => modalAlta.classList.add('oculta'));
    $('#btnCancelarModalAlta').addEventListener('click', () => modalAlta.classList.add('oculta'));

    $('#btnCerrarModalEditar').addEventListener('click', () => modalEditar.classList.add('oculta'));
    $('#btnCancelarModalEditar').addEventListener('click', () => modalEditar.classList.add('oculta'));

    // Carga inicial
    await cargarCategorias();
    await obtenerDocumentos();
});

```

---

## Ruta: `frontend\js\login.js`

```javascript
document.addEventListener('DOMContentLoaded', () => {
    const formulario = document.querySelector('#loginForm');
    const inputEmail = document.querySelector('#email');
    const inputPassword = document.querySelector('#password');
    const btnIngresar = document.querySelector('#btnIngresar');
    const cajaAlerta = document.querySelector('#alerta');

    const mostrarAlerta = (mensaje, tipo = 'error') => {
        cajaAlerta.textContent = mensaje;
        cajaAlerta.classList.remove('oculta', 'error', 'exito');
        cajaAlerta.classList.add(tipo);
    };

    const limpiarAlerta = () => {
        cajaAlerta.textContent = '';
        cajaAlerta.classList.add('oculta');
    };

    formulario.addEventListener('submit', async (evento) => {
        evento.preventDefault();
        limpiarAlerta();

        const email = inputEmail.value.trim();
        const password = inputPassword.value.trim();

        if (!email || !password) {
            mostrarAlerta('Por favor ingrese su correo y contraseña.', 'error');
            return;
        }

        btnIngresar.disabled = true;
        btnIngresar.textContent = 'Verificando...';

        try {
            const respuesta = await fetch('../api/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email, password })
            });

            const datos = await respuesta.json();

            if (!respuesta.ok) {
                throw new Error(datos.mensaje || 'Error al autenticar credenciales.');
            }

            mostrarAlerta('Acceso correcto. Redirigiendo...', 'exito');

            setTimeout(() => {
                window.location.href = 'html/panel.html';
            }, 800);

        } catch (error) {
            mostrarAlerta(error.message, 'error');
        } finally {
            btnIngresar.disabled = false;
            btnIngresar.textContent = 'Ingresar como Funcionario';
        }
    });
});

```

---

## Ruta: `frontend\js\panel.js`

```javascript
document.addEventListener('DOMContentLoaded', async () => {
    const user = await window.authPromise;
    if (!user) return;

    const cardUsuarios = document.querySelector('#cardUsuarios');

    // Muestra el módulo de gestión de usuarios únicamente al Administrador (SUPERADMIN_IT)
    if (user.rol === 'SUPERADMIN_IT') {
        if (cardUsuarios) {
            cardUsuarios.style.display = 'flex';
        }
    }
});

```

---

## Ruta: `frontend\js\portal-invitados.js`

```javascript
document.addEventListener('DOMContentLoaded', () => {
    // Selectores
    const inputBuscar = document.querySelector('#inputBuscar');
    const categoryTabs = document.querySelector('#categoryTabs');
    const documentsGrid = document.querySelector('#documentsGrid');
    const activeSurveysGrid = document.querySelector('#activeSurveysGrid');

    // Selectores del modal de encuestas
    const modalEncuesta = document.querySelector('#modalEncuesta');
    const btnCerrarModalEncuesta = document.querySelector('#btnCerrarModalEncuesta');
    const btnCancelarEncuesta = document.querySelector('#btnCancelarEncuesta');
    const formEncuesta = document.querySelector('#formEncuesta');
    const surveyTitle = document.querySelector('#surveyTitle');
    const surveyIdEncuesta = document.querySelector('#survey_id_encuesta');
    const surveyQuestionsContainer = document.querySelector('#surveyQuestionsContainer');
    const surveyObservaciones = document.querySelector('#survey_observaciones');
    const btnEnviarEncuesta = document.querySelector('#btnEnviarEncuesta');

    //Estado local
    let documentosList = [];
    let categoriasList = [];
    let encuestasActivasList = [];
    let activeCategory = 0;

    //Cargar documentos del portal público
    const cargarPortal = async () => {
        documentsGrid.innerHTML = `
            <div class="loader-box">
                <div class="spinner"></div>
                <p>Consultando documentos...</p>
            </div>
        `;

        try {
            // El endpoint api/documentos/publicos.php retorna todos los documentos de acceso libre
            const res = await fetch('../../api/documentos/publicos.php', { method: 'GET' });
            const data = await res.json();

            if (!res.ok) {
                throw new Error(data.mensaje || 'Error al conectar con la base de datos.');
            }

            documentosList = data.documentos;
            renderizarDocumentos();

        } catch (error) {
            documentsGrid.innerHTML = `
                <div style="text-align:center; padding: 2rem; color:#ef4444;">
                    <strong>✕ Error:</strong> ${error.message}
                </div>
            `;
        }
    };

    // 2. Renderizar los documentos con filtros en el cliente 
    const renderizarDocumentos = () => {
        const busqueda = inputBuscar.value.toLowerCase().trim();

        const docsFiltrados = documentosList.filter((doc) => {
            const coincideBusqueda = doc.titulo.toLowerCase().includes(busqueda);
            const coincideCategoria = activeCategory === 0 || doc.id_categoria === activeCategory;
            return coincideBusqueda && coincideCategoria;
        });

        documentsGrid.innerHTML = '';

        if (docsFiltrados.length === 0) {
            documentsGrid.innerHTML = `
                <div style="text-align:center; padding: 3rem 1rem; color:#64748b; font-size:0.9rem;">
                    No hay documentos disponibles para su búsqueda en esta categoría.
                </div>
            `;
            return;
        }

        docsFiltrados.forEach((doc) => {
            const docCard = document.createElement('article');
            docCard.className = 'doc-card';

            

            // Todos los documentos desbloqueados de acceso libre
            const actionButton = `
                <a href="../../${doc.archivo}" target="_blank" class="btn-doc-download">
                    📄 Ver / Descargar (PDF)
                </a>
            `;

            docCard.innerHTML = `
                <div class="doc-info-top">
                    <span class="doc-badge-cat">${doc.categoria_nombre}</span>
                    
                </div>
                <h3>${doc.titulo}</h3>
                <div class="doc-card-actions">
                    ${actionButton}
                    <button class="btn-survey-trigger" data-categoria="${doc.id_categoria}" data-cat-nombre="${doc.categoria_nombre}">
                        ★ Responder Encuesta
                    </button>
                </div>
            `;

            documentsGrid.appendChild(docCard);
        });
    };

    //Cargar las categorías para las pestañas de filtro
    const cargarFiltrosCategorias = async () => {
        try {
            const res = await fetch('../../api/categorias/index.php', { method: 'GET' });
            const data = await res.json();

            if (res.ok && data.status === 'ok') {
                categoriasList = data.datos;
                
                categoriasList.forEach((cat) => {
                    const tabBtn = document.createElement('button');
                    tabBtn.className = 'tab-btn';
                    tabBtn.dataset.category = cat.id_categoria;
                    tabBtn.textContent = cat.nombre.split(' y ')[0]; //Nombre corto para celular
                    categoryTabs.appendChild(tabBtn);
                });
            }
        } catch (error) {
            console.error('Fallo al cargar categorías', error);
        }
    };

    // Cargar encuestas activas para mostrarlas directamente en el portal
    const cargarEncuestasActivas = async () => {
        if (!activeSurveysGrid) return;
        try {
            const res = await fetch('../../api/encuestas/index.php?activas=1', { method: 'GET' });
            const data = await res.json();
            if (res.ok && data.status === 'ok') {
                encuestasActivasList = data.encuestas || [];
                renderizarEncuestasActivas();
            }
        } catch (error) {
            console.error('Error al cargar encuestas activas:', error);
        }
    };

    const renderizarEncuestasActivas = () => {
        if (!activeSurveysGrid) return;
        const busqueda = inputBuscar.value.toLowerCase().trim();

        const filtradas = encuestasActivasList.filter((enc) => {
            const coincideCategoria = activeCategory === 0 || parseInt(enc.id_categoria, 10) === activeCategory;
            const texto = `${enc.comentarios || ''} ${enc.categoria_nombre || ''}`.toLowerCase();
            return coincideCategoria && texto.includes(busqueda);
        });

        if (filtradas.length === 0) {
            activeSurveysGrid.innerHTML = `
                <div style="text-align:center; padding: 1.25rem; color:#64748b; font-size:0.9rem;">
                    No hay encuestas activas para el filtro seleccionado.
                </div>
            `;
            return;
        }

        activeSurveysGrid.innerHTML = filtradas.map((enc) => `
            <article class="doc-card">
                <div class="doc-info-top">
                    <span class="doc-badge-cat">${enc.categoria_nombre}</span>
                </div>
                <h3>${enc.comentarios || 'Encuesta de Satisfacción'}</h3>
                <div class="doc-card-actions">
                    <button type="button" class="btn-survey-trigger" data-encuesta="${enc.id_encuesta}" data-cat-nombre="${enc.categoria_nombre}">
                        ★ Responder Encuesta (${enc.total_preguntas || 0} preguntas)
                    </button>
                </div>
            </article>
        `).join('');
    };

    //Filtros por categorías
    categoryTabs.addEventListener('click', (e) => {
        if (e.target.classList.contains('tab-btn')) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            e.target.classList.add('active');
            activeCategory = parseInt(e.target.dataset.category, 10);
            renderizarDocumentos();
            renderizarEncuestasActivas();
        }
    });

    // Buscador interactivo
    inputBuscar.addEventListener('input', () => {
        renderizarDocumentos();
        renderizarEncuestasActivas();
    });

    // LOGICA DE ENCUESTAS DE SATISFACCION DINÁMICAS
    
    // Objeto para almacenar las respuestas temporales de los botones de escala
    let respuestasEscala = {};

    const abrirEncuesta = async (idCategoria, catNombre, idEncuestaEspecifica = 0) => {
        surveyQuestionsContainer.innerHTML = '<p class="text-center">Cargando preguntas de satisfacción...</p>';
        formEncuesta.classList.add('oculta');
        modalEncuesta.classList.remove('oculta');
        respuestasEscala = {}; //resetear escala

        try {
            const url = idEncuestaEspecifica > 0
                ? `../../api/encuestas/index.php?id_encuesta=${idEncuestaEspecifica}`
                : `../../api/encuestas/index.php?id_categoria=${idCategoria}`;
            const res = await fetch(url);
            const data = await res.json();

            if (!res.ok) throw new Error(data.mensaje || 'Error al obtener la encuesta.');

            if (!data.encuesta) {
                surveyQuestionsContainer.innerHTML = `
                    <div style="text-align:center; padding: 1rem; color:#475569;">
                        No hay ninguna encuesta activa para el área: <strong>${catNombre}</strong> en este momento.
                    </div>
                `;
                btnCancelarEncuesta.textContent = "Volver";
                document.querySelector('#btnEnviarEncuesta').classList.add('oculta');
                return;
            }

            if (!data.encuesta.preguntas || data.encuesta.preguntas.length === 0) {
                surveyTitle.textContent = data.encuesta.comentarios || `Servicio: ${catNombre}`;
                surveyQuestionsContainer.innerHTML = `
                    <div style="text-align:center; padding: 1rem; color:#475569;">
                        La encuesta <strong>${data.encuesta.comentarios || catNombre}</strong> está activa pero aún no tiene preguntas configuradas.
                    </div>
                `;
                btnCancelarEncuesta.textContent = "Volver";
                document.querySelector('#btnEnviarEncuesta').classList.add('oculta');
                formEncuesta.classList.remove('oculta');
                return;
            }

            // Encuesta encontrada
            surveyTitle.textContent = data.encuesta.comentarios
                ? `${data.encuesta.comentarios} — ${catNombre}`
                : `Servicio: ${catNombre}`;
            surveyIdEncuesta.value = data.encuesta.id_encuesta;
            btnCancelarEncuesta.textContent = "Cancelar";
            document.querySelector('#btnEnviarEncuesta').classList.remove('oculta');
            surveyQuestionsContainer.innerHTML = '';

            // Renderizar cada pregunta dinámicamente según su tipo
            data.encuesta.preguntas.forEach((pregunta, index) => {
                const block = document.createElement('div');
                block.className = 'question-block';
                block.dataset.id_pregunta = pregunta.id_pregunta;
                block.dataset.tipo = pregunta.tipo;

                const text = document.createElement('p');
                text.className = 'question-text';
                text.textContent = `${index + 1}. ${pregunta.enunciado}`;
                block.appendChild(text);

                // Renderizar inputs según el tipo de pregunta
                if (pregunta.tipo === 'Si/No') {
                    block.innerHTML += `
                        <div class="radio-options-row">
                            <label class="option-radio-label">
                                <input type="radio" name="q_${pregunta.id_pregunta}" value="Sí" required> Sí
                            </label>
                            <label class="option-radio-label">
                                <input type="radio" name="q_${pregunta.id_pregunta}" value="No" required> No
                            </label>
                        </div>
                    `;
                } else if (pregunta.tipo === 'Escala_1_5') {
                    // Contenedor de la escala
                    const scaleRow = document.createElement('div');
                    scaleRow.className = 'scale-options-row';
                    scaleRow.innerHTML = `
                        <span class="scale-label">Muy insatisfecho</span>
                        <div class="scale-items" data-pregunta="${pregunta.id_pregunta}">
                            <button type="button" class="scale-item-btn" data-val="1">1</button>
                            <button type="button" class="scale-item-btn" data-val="2">2</button>
                            <button type="button" class="scale-item-btn" data-val="3">3</button>
                            <button type="button" class="scale-item-btn" data-val="4">4</button>
                            <button type="button" class="scale-item-btn" data-val="5">5</button>
                        </div>
                        <span class="scale-label">Muy satisfecho</span>
                    `;
                    block.appendChild(scaleRow);
                } else if (pregunta.tipo === 'Texto Libre') {
                    block.innerHTML += `
                        <textarea name="q_${pregunta.id_pregunta}" rows="2" placeholder="Escriba aquí su respuesta..."></textarea>
                    `;
                } else if (pregunta.tipo === 'Opcion_Multiple') {
                    const opciones = (pregunta.opciones || []).map((op) => `
                        <label class="option-radio-label">
                            <input type="radio" name="q_${pregunta.id_pregunta}" value="${String(op.texto_opcion).replace(/"/g, '&quot;')}" required>
                            ${String(op.texto_opcion).replace(/</g, '&lt;')}
                        </label>
                    `).join('');
                    block.innerHTML += `<div class="radio-options-row" style="flex-direction:column; gap:0.5rem;">${opciones}</div>`;
                }

                surveyQuestionsContainer.appendChild(block);
            });

            formEncuesta.classList.remove('oculta');

        } catch (error) {
            surveyQuestionsContainer.innerHTML = `
                <div style="text-align:center; padding: 1rem; color:#ef4444;">
                    Error al cargar encuesta: ${error.message}
                </div>
            `;
        }
    };

    // Manejar los clicks en el botón de encuestas (en documentos o en la sección de encuestas activas)
    documentsGrid.addEventListener('click', (e) => {
        if (e.target.classList.contains('btn-survey-trigger')) {
            const idCat = e.target.dataset.categoria;
            const catNombre = e.target.dataset.catNombre;
            abrirEncuesta(idCat, catNombre);
        }
    });

    if (activeSurveysGrid) {
        activeSurveysGrid.addEventListener('click', (e) => {
            const btn = e.target.closest('.btn-survey-trigger');
            if (btn) {
                const idEnc = parseInt(btn.dataset.encuesta, 10);
                const catNombre = btn.dataset.catNombre;
                abrirEncuesta(0, catNombre, idEnc);
            }
        });
    }

    // Escuchar selección de botones de la Escala 1-5 (Delegación de eventos)
    surveyQuestionsContainer.addEventListener('click', (e) => {
        if (e.target.classList.contains('scale-item-btn')) {
            const btn = e.target;
            const container = btn.parentElement;
            const idPregunta = container.dataset.pregunta;
            const val = btn.dataset.val;

            //Deseleccionar otros botones en el mismo contenedor
            container.querySelectorAll('.scale-item-btn').forEach(b => b.classList.remove('selected'));
            
            //Seleccionar el actual
            btn.classList.add('selected');
            
            //Guardar valor en el estado temporal
            respuestasEscala[idPregunta] = val;
        }
    });

    // Enviar encuesta
    formEncuesta.addEventListener('submit', async (e) => {
        e.preventDefault();

        const idEncuesta = parseInt(surveyIdEncuesta.value, 10);
        const observaciones = surveyObservaciones.value.trim();
        const respuestas = [];

        // Validar y recopilar las respuestas de las preguntas dinámicas
        const bloques = surveyQuestionsContainer.querySelectorAll('.question-block');
        let errorValidacion = false;

        bloques.forEach((bloque) => {
            const idPregunta = parseInt(bloque.dataset.id_pregunta, 10);
            const tipo = bloque.dataset.tipo;
            let respuestaValor = '';

            if (tipo === 'Si/No' || tipo === 'Opcion_Multiple') {
                const seleccionado = bloque.querySelector(`input[name="q_${idPregunta}"]:checked`);
                if (!seleccionado) {
                    errorValidacion = true;
                } else {
                    respuestaValor = seleccionado.value;
                }
            } else if (tipo === 'Escala_1_5') {
                if (!respuestasEscala[idPregunta]) {
                    errorValidacion = true;
                } else {
                    respuestaValor = respuestasEscala[idPregunta];
                }
            } else if (tipo === 'Texto Libre') {
                const textarea = bloque.querySelector(`textarea[name="q_${idPregunta}"]`);
                respuestaValor = textarea.value.trim();
            }

            respuestas.push({
                id_pregunta: idPregunta,
                contenido_respuesta: respuestaValor
            });
        });

        if (errorValidacion) {
            alert('Por favor responda todas las preguntas con opción o calificación antes de enviar.');
            return;
        }

        btnEnviarEncuesta.disabled = true;
        btnEnviarEncuesta.textContent = 'Enviando...';

        try {
            const resSubmit = await fetch('../../api/encuestas/index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id_encuesta: idEncuesta,
                    observaciones: observaciones,
                    respuestas: respuestas
                })
            });

            const dataRes = await resSubmit.json();

            if (!resSubmit.ok) {
                throw new Error(dataRes.mensaje || 'Error al guardar respuestas.');
            }

            alert(dataRes.mensaje);
            modalEncuesta.classList.add('oculta');
            formEncuesta.reset();

        } catch (err) {
            alert('Error al enviar la encuesta: ' + err.message);
        } finally {
            btnEnviarEncuesta.disabled = false;
            btnEnviarEncuesta.textContent = 'Enviar Respuestas';
        }
    });

    // Control de cierre del modal de encuestas
    btnCerrarModalEncuesta.addEventListener('click', () => modalEncuesta.classList.add('oculta'));
    btnCancelarEncuesta.addEventListener('click', () => modalEncuesta.classList.add('oculta'));

    // Inicialización del portal
    cargarFiltrosCategorias();
    cargarPortal();
    cargarEncuestasActivas();
});

```

---

## Ruta: `frontend\js\usuarios.js`

```javascript
document.addEventListener('DOMContentLoaded', () => {
    // Selectores del DOM
    const txtNombre = document.querySelector('#txtNombre');
    const txtRol = document.querySelector('#txtRol');
    const btnAbrirModalAlta = document.querySelector('#btnAbrirModalAlta');
    const inputBuscar = document.querySelector('#inputBuscar');
    const tablaCuerpo = document.querySelector('#tablaCuerpo');
    const alerta = document.querySelector('#alerta');

    // Selectores del modal Alta
    const modalAlta = document.querySelector('#modalAlta');
    const btnCerrarModalAlta = document.querySelector('#btnCerrarModalAlta');
    const btnCancelarModalAlta = document.querySelector('#btnCancelarModalAlta');
    const formAltaUsuario = document.querySelector('#formAltaUsuario');
    const btnGuardarUsuario = document.querySelector('#btnGuardarUsuario');

    // Selectores del modal Editar
    const modalEditar = document.querySelector('#modalEditar');
    const btnCerrarModalEditar = document.querySelector('#btnCerrarModalEditar');
    const btnCancelarModalEditar = document.querySelector('#btnCancelarModalEditar');
    const formEditarUsuario = document.querySelector('#formEditarUsuario');
    const btnActualizarUsuario = document.querySelector('#btnActualizarUsuario');

    // Campos modal Editar
    const editIdUsuario = document.querySelector('#edit_id_usuario');
    const editNombre = document.querySelector('#edit_nombre');
    const editEmail = document.querySelector('#edit_email');
    const editContrasenia = document.querySelector('#edit_contrasenia');
    const editRol = document.querySelector('#edit_rol');
    const editEstado = document.querySelector('#edit_estado');

    // Estado local
    let usuariosList = [];
    let currentUser = null;

    // Utilidad: Mostrar Alertas
    const mostrarAlerta = (mensaje, tipo = 'exito') => {
        alerta.textContent = mensaje;
        alerta.className = `alerta alerta-${tipo}`;
        alerta.classList.remove('oculta');
        setTimeout(() => alerta.classList.add('oculta'), 5000);
    };

    // Inicializar verificación de sesión usando authPromise
    window.authPromise.then(async (user) => {
        if (!user) return;
        currentUser = user;

        // Solo SUPERADMIN_IT puede ver el botón de registrar funcionarios
        if (currentUser.rol === 'SUPERADMIN_IT') {
            if (btnAbrirModalAlta) {
                btnAbrirModalAlta.style.display = 'inline-block';
            }
        }

        await cargarUsuarios();
    });

    // Cargar listado de usuarios de la API
    const cargarUsuarios = async () => {
        try {
            const res = await fetch('../../api/usuarios/index.php');
            const data = await res.json();

            if (!res.ok) {
                throw new Error(data.mensaje || 'Error al recuperar personal.');
            }

            usuariosList = data.usuarios;
            renderizarUsuarios();

        } catch (error) {
            tablaCuerpo.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-danger">
                        ✕ Error al cargar: ${error.message}
                    </td>
                </tr>
            `;
        }
    };

    // Renderizar filas en la tabla con el buscador en el cliente 
    const renderizarUsuarios = () => {
        const busqueda = inputBuscar.value.toLowerCase().trim();

        const usuariosFiltrados = usuariosList.filter(user => {
            return user.nombre.toLowerCase().includes(busqueda) || 
                   user.email.toLowerCase().includes(busqueda) ||
                   user.rol.toLowerCase().includes(busqueda);
        });

        tablaCuerpo.innerHTML = '';

        if (usuariosFiltrados.length === 0) {
            tablaCuerpo.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-muted">
                        No se encontraron funcionarios registrados.
                    </td>
                </tr>
            `;
            return;
        }

        usuariosFiltrados.forEach(user => {
            const tr = document.createElement('tr');
            
            const badgeStatus = user.estado === 'Activo' 
                ? '<span class="badge-status-activo">Activo</span>' 
                : '<span class="badge-status-inactivo">Inactivo</span>';

            // Mostrar botones de Editar y Eliminar solo para administradores
            let btnAcciones = '';
            if (currentUser && currentUser.rol === 'SUPERADMIN_IT') {
                const btnEdit = `<button class="btn-action btn-edit-usuario" data-id="${user.id_usuario}">✏️ Editar</button>`;
                
                let btnDel = '';
                if (currentUser.id_usuario === user.id_usuario) {
                    btnDel = `<span class="text-muted" style="font-size: 0.8rem; margin-left: 0.5rem;">(Tu Cuenta)</span>`;
                } else {
                    btnDel = `
                        <button class="btn-action btn-delete btn-eliminar-usuario" data-id="${user.id_usuario}" data-nombre="${user.nombre}">
                            🗑️ Eliminar
                        </button>
                    `;
                }
                btnAcciones = `<div style="display: flex; gap: 0.5rem; align-items: center;">${btnEdit} ${btnDel}</div>`;
            } else {
                btnAcciones = `<span class="text-muted" style="font-size: 0.8rem;">Sin Permisos</span>`;
            }

            tr.innerHTML = `
                <td><strong>#${user.id_usuario}</strong></td>
                <td>${user.nombre}</td>
                <td><a href="mailto:${user.email}">${user.email}</a></td>
                <td><span class="badge-rol" style="background-color: #64748b; font-size: 0.75rem;">${user.rol}</span></td>
                <td>${badgeStatus}</td>
                <td>${btnAcciones}</td>
            `;

            tablaCuerpo.appendChild(tr);
        });
    };

    // 4. Modal de Alta: Abrir y Cerrar
    btnAbrirModalAlta.addEventListener('click', () => {
        formAltaUsuario.reset();
        modalAlta.classList.remove('oculta');
    });

    const cerrarModalAlta = () => {
        modalAlta.classList.add('oculta');
        formAltaUsuario.reset();
    };

    btnCerrarModalAlta.addEventListener('click', cerrarModalAlta);
    btnCancelarModalAlta.addEventListener('click', cerrarModalAlta);

    // 5. Submit del formulario de alta (POST asíncrono)
    formAltaUsuario.addEventListener('submit', async (e) => {
        e.preventDefault();

        const nombre = document.querySelector('#nombre').value.trim();
        const email = document.querySelector('#email').value.trim();
        const contrasenia = document.querySelector('#contrasenia').value;
        const rol = document.querySelector('#rol').value;
        const estado = document.querySelector('#estado').value;

        if (!nombre || !email || !contrasenia || !rol || !estado) {
            alert('Por favor complete todos los campos obligatorios (*).');
            return;
        }

        if (contrasenia.length < 6) {
            alert('La contraseña temporal debe tener al menos 6 caracteres.');
            return;
        }

        btnGuardarUsuario.disabled = true;
        btnGuardarUsuario.textContent = 'Guardando...';

        try {
            const res = await fetch('../../api/usuarios/index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    nombre,
                    email,
                    contrasenia,
                    rol,
                    estado
                })
            });

            const data = await res.json();

            if (!res.ok) {
                throw new Error(data.mensaje || 'Error al procesar la solicitud.');
            }

            mostrarAlerta(data.mensaje, 'exito');
            cerrarModalAlta();
            await cargarUsuarios(); // Recargar grilla

        } catch (error) {
            alert('✕ Error: ' + error.message);
        } finally {
            btnGuardarUsuario.disabled = false;
            btnGuardarUsuario.textContent = 'Crear Funcionario';
        }
    });

    // Modal de Edición: Abrir, Rellenar y Cerrar
    const cerrarModalEditar = () => {
        modalEditar.classList.add('oculta');
        formEditarUsuario.reset();
    };

    btnCerrarModalEditar.addEventListener('click', cerrarModalEditar);
    btnCancelarModalEditar.addEventListener('click', cerrarModalEditar);

    // Delegación de clicks (Editar y Eliminar)
    tablaCuerpo.addEventListener('click', async (e) => {
        // Click en Editar
        if (e.target.classList.contains('btn-edit-usuario')) {
            const id = parseInt(e.target.dataset.id, 10);
            const user = usuariosList.find(u => u.id_usuario === id);

            if (user) {
                editIdUsuario.value = user.id_usuario;
                editNombre.value = user.nombre;
                editEmail.value = user.email;
                editContrasenia.value = ''; //en blanco por defecto
                editRol.value = user.rol;
                editEstado.value = user.estado;
                
                modalEditar.classList.remove('oculta');
            }
        }

        // Click en Eliminar
        if (e.target.classList.contains('btn-eliminar-usuario')) {
            const id = e.target.dataset.id;
            const nombre = e.target.dataset.nombre;

            if (confirm(`¿Está seguro que desea eliminar definitivamente al funcionario "${nombre}"?\nEsta acción no se puede deshacer.`)) {
                try {
                    const res = await fetch(`../../api/usuarios/index.php?id=${id}`, {
                        method: 'DELETE'
                    });

                    const data = await res.json();

                    if (!res.ok) {
                        throw new Error(data.mensaje || 'Error al intentar eliminar.');
                    }

                    mostrarAlerta(data.mensaje, 'exito');
                    await cargarUsuarios();

                } catch (error) {
                    alert('✕ Error: ' + error.message);
                }
            }
        }
    });

    // Submit del formulario de Edición (POST asíncrono)
    formEditarUsuario.addEventListener('submit', async (e) => {
        e.preventDefault();

        const id_usuario = parseInt(editIdUsuario.value, 10);
        const nombre = editNombre.value.trim();
        const email = editEmail.value.trim();
        const contrasenia = editContrasenia.value;
        const rol = editRol.value;
        const estado = editEstado.value;

        if (!nombre || !email || !rol || !estado) {
            alert('Por favor complete todos los campos obligatorios (*).');
            return;
        }

        if (contrasenia && contrasenia.length < 6) {
            alert('La nueva contraseña debe tener al menos 6 caracteres.');
            return;
        }

        btnActualizarUsuario.disabled = true;
        btnActualizarUsuario.textContent = 'Actualizando...';

        try {
            const res = await fetch(`../../api/usuarios/index.php?id=${id_usuario}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id_usuario,
                    nombre,
                    email,
                    contrasenia,
                    rol,
                    estado
                })
            });

            const data = await res.json();

            if (!res.ok) {
                throw new Error(data.mensaje || 'Error al procesar la actualización.');
            }

            mostrarAlerta(data.mensaje, 'exito');
            cerrarModalEditar();
            await cargarUsuarios(); //Recargar grilla

        } catch (error) {
            alert('✕ Error: ' + error.message);
        } finally {
            btnActualizarUsuario.disabled = false;
            btnActualizarUsuario.textContent = 'Guardar Cambios';
        }
    });

    //Buscador interactivo
    inputBuscar.addEventListener('input', renderizarUsuarios);
});

```

---

