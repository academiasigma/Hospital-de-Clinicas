<?php

require_once __DIR__ . '/Auth.php';

date_default_timezone_set('America/Montevideo');

// funciones generales para usar en las apis
class Api {

    // inicia la sesion y verifica que el metodo http sea valido
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

    // devuelve la respuesta en json
    public static function responder(int $codigo, array $datos): void {
        http_response_code($codigo);
        echo json_encode($datos, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // devuelve un mensaje de error con su codigo
    public static function error(int $codigo, string $mensaje, array $errores = []): void {
        $respuesta = ['status' => 'error', 'mensaje' => $mensaje];
        if (!empty($errores)) {
            $respuesta['errores'] = $errores;
        }
        self::responder($codigo, $respuesta);
    }

    // lee los datos json que llegan en el body
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

    // revisa que el usuario haya iniciado sesion y tenga el rol necesario
    public static function exigirRol(array $rolesPermitidos): void {
        if (!Auth::verificarAutenticacion()) {
            self::error(401, 'No autorizado. Debe iniciar sesión.');
        }
        if (!Auth::verificarRol($rolesPermitidos)) {
            self::error(403, 'Privilegios insuficientes para esta operación.');
        }
    }

    // saca el id que viene por parametro o en la url
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

    // abm general para choferes, enfermeros, vehiculos y pacientes
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

        // eliminar registro
        if (!$modelo->eliminar($id)) {
            self::errorDeModelo($modelo, $etiqueta);
        }
        self::responder(200, ['status' => 'ok', 'mensaje' => "$etiqueta eliminado."]);
    }

    // muestra el error segun lo que fallo en la base
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
