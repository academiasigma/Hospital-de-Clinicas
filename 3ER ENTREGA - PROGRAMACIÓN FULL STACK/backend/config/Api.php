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
