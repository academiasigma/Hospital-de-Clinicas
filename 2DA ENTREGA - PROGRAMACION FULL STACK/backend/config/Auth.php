<?php
class Auth {
    
     //Inicia la sesión PHP con parámetros de configuración de cookies seguras.
      public static function iniciarSesion(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start([
                'cookie_httponly' => true,
                'cookie_samesite' => 'Lax'
            ]);
        }
    }

     //Valida si existe un usuario autenticado en la sesión actual.
    public static function verificarAutenticacion(): bool {
        self::iniciarSesion();
        return isset($_SESSION['usuario']) && !empty($_SESSION['usuario']['id_usuario']);
    }

     //Valida que el rol del usuario conectado coincida con los usuarios permitidos para la operación.
    public static function verificarRol(array $rolesPermitidos): bool {
        if (!self::verificarAutenticacion()) {
            return false;
        }

        return in_array($_SESSION['usuario']['rol'], $rolesPermitidos, true);
    }
}
