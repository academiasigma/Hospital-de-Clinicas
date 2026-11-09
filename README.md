# 🏥 S.I.G.S.M. — Sistema Informático de Gestión de Servicios Médicos

**S.I.G.S.M.** es una plataforma web integral desarrollada para el **Hospital de Clínicas "Dr. Manuel Quintela"**, creada por el equipo **Academia Sigma** como proyecto final de egreso del **Bachillerato Tecnológico en Tecnologías de la Información (UTU ISBO, 2026)**.

El sistema centraliza y digitaliza dos áreas críticas del hospital: la **coordinación y trazabilidad en tiempo real de traslados sanitarios (ambulancias, flota y recursos asistenciales)** y la **gestión de documentación clínica informativa en PDF junto con encuestas anónimas de satisfacción del paciente**, bajo una arquitectura estricta de **Tres Capas (CSR + API RESTful)** orquestada mediante contenedores **Docker** sobre **CentOS Stream 9**.

---

## 🎯 Objetivo del Proyecto

Optimizar los procesos operativos, logísticos y de comunicación clínica del Hospital de Clínicas mediante una solución web accesible, segura y de alta disponibilidad.  
El proyecto consolida los conocimientos de **Programación Full Stack**, **Diseño e Implementación de Bases de Datos Relacionales (UTULAB)**, **Administración de Sistemas Operativos** y **Gestión de Proyectos**, aplicando buenas prácticas de ingeniería de software, seguridad criptográfica y despliegue en contenedores.

---

## ✨ Características Principales

* **🚑 Módulo de Gestión de Traslados y Flota Sanitaria:**
  * Solicitud y despacho de traslados de **pacientes** (con autocompletado por C.I. y requerimientos clínicos: oxígeno, camilla, aislamiento, autonomía) y **objetos médicos** (órganos para trasplante con prioridad alta automática y hora límite, muestras biológicas e insumos).
  * Validación automática de **no solapamiento horario** de vehículos, choferes y enfermeros, restricción geográfica metropolitana y máquina de estados (`Pendiente` → `En Curso` → `Retornando` → `Finalizado` / `Cancelado`).
  * **Terminal Móvil de Chofer** con interfaz adaptable (*Mobile-First*) para el registro en tiempo real de horarios de salida, llegada efectiva a destino y retorno al hospital.
  * **ABM completo de Recursos Operativos:** gestión de choferes, enfermeros acompañantes y flota de vehículos (`Ambulancia` / `Auto`).
* **📄 Módulo de Documentación Clínica y Categorías:**
  * Publicación, edición, reemplazo y baja física/lógica de guías médicas e instructivos en formato **PDF** con inspección binaria de tipo MIME real (`application/pdf` y cabecera `%PDF-`).
  * **ABM de Categorías Clínicas** con protección de integridad referencial (`ON DELETE RESTRICT` / `HTTP 409 Conflict` ante categorías con documentos asociados).
* **📊 Módulo de Encuestas de Satisfacción del Paciente:**
  * Creación y administración de encuestas por categoría médica y configuración dinámica de preguntas (`Si/No`, `Escala_1_5`, `Opcion_Multiple` y `Texto Libre`).
  * **Portal Público de Invitados** de libre acceso para que los pacientes consulten guías médicas y respondan encuestas de forma **100% anónima y transaccional**.
  * Panel estadístico en tiempo real con cálculo de promedios ponderados, porcentajes, distribución de calificaciones y listado de observaciones.
* **🔐 Control de Acceso Basado en Roles (RBAC):**
  * Autenticación mediante sesiones PHP seguras y contraseñas encriptadas con **BCRYPT** (`password_hash` / `password_verify`), diferenciando permisos entre `SUPERADMIN_IT`, `GESTOR_INTEGRAL`, `DIRECTOR_GENERAL` y `USUARIO_DIVISION`.

---

## 🧠 Tecnologías Utilizadas

### Frontend (Capa de Presentación)
* **HTML5 Semántico** y **CSS3 Moderno** (CSS Grid, Flexbox, variables personalizadas).
* **JavaScript Nativo (ES6+):** consumo asíncrono de la API REST mediante `Fetch API` (`async/await`), manipulación segura del DOM y renderizado en el cliente (*Client-Side Rendering*) sin uso de `localStorage`.
* **Diseño Responsivo (*Mobile-First*):** adaptado desde dispositivos móviles (`320px+`), tablets de ambulancia (`768px+`) hasta monitores de escritorio (`1024px+`).

### Backend y Persistencia (Capa de Negocio y Datos)
* **PHP 8.2:** arquitectura orientada a objetos con controladores RESTful (`GET`, `POST`, `PUT`, `DELETE`) y respuestas estandarizadas en JSON.
* **PDO (PHP Data Objects):** uso exclusivo de sentencias preparadas parametrizadas para prevenir inyección SQL (SQLi).
* **MariaDB 10.5 / MySQL:** base de datos relacional normalizada hasta **3FN** (`sigsm_hospital`, 14 tablas en motor `InnoDB` con codificación `utf8mb4`).

### Infraestructura y Control de Versiones
* **Docker y Docker Compose:** separación en contenedor de aplicación (`sigsm_web` con Apache + PHP 8.2 + `mod_rewrite`) y contenedor de base de datos (`sigsm_mariadb` con volumen persistente y *healthcheck*).
* **Servidor de Producción:** máquina virtual sobre **CentOS Stream 9**.
* **Git y GitHub:** control de versiones distribuido bajo convenciones de *commits* descriptivos.

---

## 📁 Organización del Repositorio por Entregas

El repositorio mantiene una estructura organizada por entregas académicas para facilitar la evaluación docente y la trazabilidad de la evolución del sistema:

| Directorio en el Repositorio | Descripción |
| :--- | :--- |
| **`1era Entrega Programacion Full Stack/`** | Maquetación inicial del sistema, prototipos de interfaz y primera aproximación de diseño. |
| **`2da Entrega Programacion Full Stack/`** | Rediseño relacional normalizado, primeros endpoints en PHP/PDO y documentación técnica de la segunda etapa. |
| **`Segunda Entrega Sistemas Operativos/`** | Scripts de administración, configuración de red y aprovisionamiento del servidor Linux CentOS. |
| **`3ER ENTREGA - PROGRAMACIÓN FULL STACK/`** | **Versión final consolidada (*Release*):** aplicación Full Stack 100% funcional y persistente en MariaDB, API REST completa, contenedorización con Docker Compose y archivo `UsuariosPrueba.txt`. |

---

## 🏗️ Estructura del Proyecto (`3ER ENTREGA - PROGRAMACIÓN FULL STACK`)

```text
3ER ENTREGA - PROGRAMACIÓN FULL STACK/
│
├── api/                              # Enrutadores RESTful expuestos por Apache (JSON)
│   ├── login.php                     # Autenticación de funcionarios con verificación BCRYPT
│   ├── logout.php                    # Destrucción segura de sesión y cookies
│   ├── sesion.php                    # Verificación de sesión activa y rol del usuario
│   ├── categorias/index.php          # CRUD de categorías clínicas (GET, POST, PUT, DELETE)
│   ├── choferes/index.php            # CRUD de choferes de locomoción
│   ├── documentos/
│   │   ├── index.php                 # CRUD de documentos PDF con validación MIME binaria
│   │   └── publicos.php              # Catálogo público de documentos para el Portal de Invitados
│   ├── encuestas/index.php           # CRUD de encuestas/preguntas, envío anónimo y resultados
│   ├── enfermeros/index.php          # CRUD de enfermeros acompañantes
│   ├── objetos/index.php             # Catálogo de órganos, muestras biológicas e insumos
│   ├── pacientes/index.php           # Búsqueda por C.I. y registro/actualización de pacientes
│   ├── traslados/index.php           # CRUD de traslados, filtros y máquina de estados/horarios
│   ├── usuarios/index.php            # CRUD de funcionarios institucionales (solo SUPERADMIN_IT)
│   └── vehiculos/index.php           # CRUD de flota sanitaria (ambulancias y autos)
│
├── backend/                          # Núcleo de la Capa de Negocio y Acceso a Datos (PDO)
│   ├── config/
│   │   ├── Api.php                   # Helper central REST (métodos HTTP, JSON, control de roles)
│   │   ├── Auth.php                  # Gestión de sesiones PHP y parámetros de seguridad
│   │   └── Database.php              # Conexión PDO multi-host (CentOS / Docker / XAMPP)
│   └── models/
│       ├── Categoria.php             # Modelo de categorías institucionales
│       ├── Chofer.php                # Modelo de choferes
│       ├── Documento.php              # Modelo de documentos clínicos y gestión física de PDFs
│       ├── Encuesta.php              # Modelo transaccional de encuestas, preguntas y estadísticas
│       ├── Enfermero.php             # Modelo de enfermeros
│       ├── Paciente.php              # Modelo de pacientes con validación y upsert por C.I.
│       ├── Recurso.php               # Clase abstracta base con operaciones CRUD preparadas PDO
│       ├── Traslado.php              # Modelo de traslados y reglas de negocio (RNE01 a RNE07)
│       ├── Usuario.php               # Modelo de usuarios y hashing BCRYPT
│       └── Vehiculo.php              # Modelo de vehículos sanitarios
│
├── frontend/                         # Capa de Presentación (HTML5, CSS3, JS Fetch API)
│   ├── index.html                    # Portal de acceso unificado (Funcionarios e Invitados)
│   ├── css/                          # Hojas de estilo modulares y responsivas
│   │   ├── style-ambulancias.css
│   │   ├── style-documentos.css
│   │   ├── style-login.css
│   │   ├── style-panel.css
│   │   └── style-portal.css
│   ├── html/                         # Vistas de la aplicación
│   │   ├── ambulancias.html          # Panel de gestión, filtros y seguimiento de traslados
│   │   ├── ambulancias-chofer.html   # Terminal móvil de trazabilidad para choferes
│   │   ├── ambulancias-recursos.html # Administración de flota, choferes y enfermeros
│   │   ├── documentos.html           # Gestión de PDFs, categorías y encuestas/resultados
│   │   ├── panel.html                # Dashboard principal dinámico según rol
│   │   ├── portal-invitados.html     # Portal público para pacientes (guías PDF y encuestas)
│   │   └── usuarios.html             # Administración de cuentas de funcionarios
│   ├── img/                          # Logotipos institucionales e iconografía SVG
│   └── js/                           # Controladores asíncronos del cliente
│       ├── ambulancias.js
│       ├── ambulancias-chofer.js
│       ├── ambulancias-recursos.js
│       ├── auth.js
│       ├── documentos.js
│       ├── login.js
│       ├── panel.js
│       ├── portal-invitados.js
│       └── usuarios.js
│
├── uploads/documentos/               # Almacenamiento físico de documentos clínicos PDF
├── .htaccess                         # Reglas mod_rewrite de Apache para rutas REST limpias
├── Dockerfile                        # Definición de imagen PHP 8.2-Apache + extensiones PDO
├── docker-compose.yml                # Orquestación de contenedores (sigsm_web y sigsm_mariadb)
├── sigsm_db.sql                      # Script integral DDL (14 tablas), DML (semilla) y DCL
├── UsuariosPrueba.txt                # Credenciales de prueba para evaluación docente
└── README.md                         # Documentación general del repositorio
```

---

## ⚙️ Requisitos e Instalación

### Opción 1: Despliegue con Docker Compose (Recomendado / Servidor CentOS Stream 9)

1. **Clonar el repositorio y ubicarse en la carpeta de la Tercera Entrega:**
   ```bash
   git clone https://github.com/academiasigma/Hospital-de-Clinicas.git
   cd "Hospital-de-Clinicas/3ER ENTREGA - PROGRAMACIÓN FULL STACK"
   ```

2. **Construir y levantar los contenedores en segundo plano:**
   ```bash
   docker compose up -d --build
   ```
   *Esto levantará automáticamente:*
   * **`sigsm_mariadb`** (MariaDB 10.5 en el puerto `3306`, importando automáticamente `sigsm_db.sql` con las 14 tablas, los datos semilla y los usuarios DCL).
   * **`sigsm_web`** (Apache + PHP 8.2 en el puerto `8080`, con `pdo_mysql`, `mod_rewrite` y permisos de escritura en `/var/www/html/uploads`).

3. **Acceder a la aplicación desde el navegador:**
   * **En máquina local:** `http://localhost:8080/frontend/index.html`
   * **En servidor CentOS Stream 9:** `http://192.168.1.234:8080/frontend/index.html`
   * **Portal Público de Invitados:** `http://192.168.1.234:8080/frontend/html/portal-invitados.html`

### Opción 2: Ejecución en Entorno Local (XAMPP)

1. Copiar el contenido de `3ER ENTREGA - PROGRAMACIÓN FULL STACK` dentro de `C:\xampp\htdocs\sigsm\`.
2. Iniciar **Apache** y **MySQL** desde el panel de control de XAMPP.
3. Importar el archivo `sigsm_db.sql` desde `http://localhost/phpmyadmin` (crea automáticamente la base de datos `sigsm_hospital`, sus 14 tablas y los usuarios `sigsm_app` y `sigsm_admin`).
4. Ingresar en el navegador a `http://localhost/sigsm/frontend/index.html`.

---

## 🔐 Seguridad implementada

* **Prevención de Inyección SQL (SQLi):** 100% de las consultas a la base de datos utilizan sentencias preparadas (`PDO::prepare` con `ATTR_EMULATE_PREPARES => false`).
* **Principio de Mínimo Privilegio en Base de Datos (DCL):** La aplicación web se conecta exclusivamente con el usuario `sigsm_app` (restringido a `SELECT`, `INSERT`, `UPDATE`, `DELETE`), separándolo del usuario administrador `sigsm_admin` (`ALL PRIVILEGES`).
* **Hashing Criptográfico de Contraseñas:** Almacenamiento mediante `password_hash(..., PASSWORD_BCRYPT)` y verificación con `password_verify()`.
* **Validación Binaria de Archivos PDF:** Inspección en servidor con `finfo_file()` (`application/pdf`), verificación de firma mágica `%PDF-` y límite de tamaño de 10 MB.
* **Protección contra XSS:** Sanitización y escape de caracteres HTML en la renderización dinámica del DOM.

---

## 🔑 Credenciales de Prueba (`UsuariosPrueba.txt`)

Todas las cuentas de prueba del sistema web tienen como contraseña: **`password`**

| Rol en el Sistema | Correo Institucional | Contraseña | Permisos Principales |
| :--- | :--- | :--- | :--- |
| **Usuario Empresa (CREA)** | `academiasigma@hc.edu.uy` | `password` | Control total del sistema (`SUPERADMIN_IT`) |
| **SuperAdmin IT** | `superadmin@hc.edu.uy` | `password` | Control total y ABM de funcionarios (`usuarios.html`) |
| **Gestor Integral** | `gestor@hc.edu.uy` | `password` | Coordinación de traslados, flota, categorías, documentos y encuestas |
| **Director General** | `director@hc.edu.uy` | `password` | Supervisión y consulta general de traslados, documentos y métricas |
| **Usuario División** | `division@hc.edu.uy` | `password` | Solicitud de traslados de su división y publicación de guías PDF |

**Credenciales de Base de Datos MariaDB (`sigsm_hospital`):**
* **Usuario de Aplicación (DML):** `sigsm_app` / Contraseña: `Hc1234_db`
* **Usuario Administrador (DDL/DCL):** `sigsm_admin` / Contraseña: `Hc1234_admin`

---

## 👨‍💻 Equipo de Desarrollo — Academia Sigma

Proyecto desarrollado por los estudiantes de **3° MN — UTU Instituto Superior Brazo Oriental (ISBO)**:

| Rol en el Equipo | Integrante | Contacto |
| :--- | :--- | :--- |
| **Coordinador** | Facundo Guedikian | `facuguedikian@gmail.com` |
| **Subcoordinador** | Ihojan Robaina | `ihojarex.r@gmail.com` |
| **Integrante 1** | Ezequiel Gómez | `gomezeze05@gmail.com` |
| **Integrante 2** | Nicolás Chaitelle | `nicolaschaitelleyapor@gmail.com` |

* **Docente de Programación Full Stack:** Prof. Carlos Romero
* **Repositorio Oficial:** [https://github.com/academiasigma/Hospital-de-Clinicas](https://github.com/academiasigma/Hospital-de-Clinicas)

---

## 📖 Licencia

Este proyecto fue desarrollado con fines **académicos y educativos** para el Bachillerato Tecnológico en Tecnologías de la Información (DGETP - UTU).  
Copyright © 2026 **Academia Sigma**. Todos los derechos reservados.
