DROP DATABASE IF EXISTS sigsm_db;
CREATE DATABASE sigsm_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sigsm_db;

-- SECCIÓN 1: TABLAS MAESTRAS  y INDEPENDIENTES

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
    codigo_qr VARCHAR(255) NOT NULL,
    es_sensible TINYINT(1) DEFAULT 0,
    id_usuario INT NOT NULL,
    id_categoria INT NOT NULL,
    CONSTRAINT CP_documento PRIMARY KEY (id_documento),
    CONSTRAINT CS_documento_codigo_qr UNIQUE (codigo_qr),
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

-- Añadida para  la 3FN en las preguntas de Opción Multiple
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

--Con el Enumerado de los estados restringidos
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
    observaciones TEXT,
    division_solicitante VARCHAR(100) NOT NULL,
    motivo_procedimiento VARCHAR(200) NOT NULL,
    responsable_recepcion VARCHAR(100) NOT NULL,
    limite_horario TIME,
    id_vehiculo INT NOT NULL,
    id_chofer INT NOT NULL,
    id_usuario INT NOT NULL,
    id_enfermero INT,
    id_paciente INT,
    id_objeto INT,
    CONSTRAINT CP_traslado PRIMARY KEY (id_traslado),
    CONSTRAINT CE_traslado_vehiculo FOREIGN KEY (id_vehiculo) REFERENCES vehiculo(id_vehiculo) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT CE_traslado_chofer FOREIGN KEY (id_chofer) REFERENCES chofer(id_chofer) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT CE_traslado_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT CE_traslado_enfermero FOREIGN KEY (id_enfermero) REFERENCES enfermero(id_enfermero) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT CE_traslado_paciente FOREIGN KEY (id_paciente) REFERENCES paciente(id_paciente) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT CE_traslado_objeto FOREIGN KEY (id_objeto) REFERENCES objeto(id_objeto) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SECCIÓN 3: SCRIPT DML (datos de prueba)

-- inserción de los Usuarios iniciales con los roles reales del sistema
-- Clave de prueba: 'password' encriptada con password_hash
INSERT INTO usuario (email, nombre, contrasenia, rol, estado) VALUES 
('superadmin@hc.edu.uy', 'SuperAdmin IT', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'SUPERADMIN_IT', 'Activo'),
('gestor@hc.edu.uy', 'Gestor Integral', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'GESTOR_INTEGRAL', 'Activo'),
('director@hc.edu.uy', 'Director General', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'DIRECTOR_GENERAL', 'Activo'),
('division@hc.edu.uy', 'Usuario Division', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'USUARIO_DIVISION', 'Activo');

-- Inserción de las categorías médicas
INSERT INTO categoria (nombre) VALUES 
('Indicaciones Médicas y Quirúrgicas'),
('Preparación para Estudios'),
('Enfermería y Trasplantes'),
('Encuestas de Satisfacción'),
('Protocolos de Prevención');

-- Datos de prueba
INSERT INTO chofer (nombre, telefono, licencia) VALUES 
('Carlos Rodríguez', '099123456', 'LIC-CH-001'),
('Mario Gómez', '098765432', 'LIC-CH-002');

INSERT INTO enfermero (nombre, telefono, matricula_profesional) VALUES 
('Ana Martínez', '091112233', 'ENF-MAT-101');

INSERT INTO vehiculo (tipo_vehiculo, matricula, estado) VALUES 
('Ambulancia', 'SCA-1001', 'Disponible'),
('Auto', 'SCA-2002', 'Disponible');

-- Pacientes prueba
INSERT INTO paciente (documento, nombre, telefono, fecha_de_nacimiento, requiere_oxigeno, requiere_camilla, requiere_aislamiento, se_maneja_por_sus_medios) VALUES 
('48889991', 'María Silva', '092333444', '1985-04-12', 0, 0, 0, 1),
('12345678', 'Juan Pérez', '099555666', '1975-08-20', 1, 0, 0, 0);

-- Documentos clínicos iniciales
INSERT INTO documento (titulo, archivo, codigo_qr, es_sensible, id_usuario, id_categoria) VALUES 
('Indicaciones de interrupción voluntaria del embarazo', 'uploads/documentos/ive_indicaciones.pdf', 'QR_SIGSM_IVE_001', 1, 1, 1),
('Preparación para ecocardiograma transesofágico', 'uploads/documentos/ecocardiograma_prep.pdf', 'QR_SIGSM_ECO_002', 0, 1, 2),
('Plan de alta de enfermería en Nefrología', 'uploads/documentos/nefrologia_alta.pdf', 'QR_SIGSM_NEF_003', 0, 1, 3);

-- Encuestas activas de prueba 
INSERT INTO encuesta (id_encuesta, comentarios, estado, id_categoria) VALUES 
(1, 'Encuesta del Área Ginecología/IVE', 'Activa', 1),
(2, 'Encuesta del Área Estudios Imagenológicos', 'Activa', 2);

-- Preguntas asociadas a la encuesta 1 (Categoría 1)
INSERT INTO pregunta (id_pregunta, enunciado, tipo, id_encuesta) VALUES 
(1, '¿La información provista en la guía clínica fue clara y fácil de entender?', 'Si/No', 1),
(2, 'Califique la calidad de la atención médica recibida durante su consulta:', 'Escala_1_5', 1),
(3, 'Sugerencias o comentarios adicionales para mejorar este instructivo:', 'Texto Libre', 1);

-- Preguntas asociadas a la encuesta 2 (Categoría 2)
INSERT INTO pregunta (id_pregunta, enunciado, tipo, id_encuesta) VALUES 
(4, '¿Le resultó sencillo seguir las indicaciones de ayuno y preparación para su estudio?', 'Si/No', 2),
(5, 'Califique la amabilidad y el respeto mostrado por el personal técnico:', 'Escala_1_5', 2),
(6, 'Deje sus comentarios sobre su experiencia general de preparación:', 'Texto Libre', 2);
