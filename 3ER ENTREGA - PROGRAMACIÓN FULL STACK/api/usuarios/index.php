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
