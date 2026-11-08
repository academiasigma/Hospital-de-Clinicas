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
    public function conectar(): ?PDO {
        $this->conexion = null;

        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8mb4";
            $opciones = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            $this->conexion = new PDO($dsn, $this->username, $this->password, $opciones);
        } catch (PDOException $e) {
            // No se expone $e->getMessage() al cliente (puede filtrar host/usuario); se registra en el log.
            error_log('Error de conexión a la base de datos: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'status'  => 'error',
                'mensaje' => 'Error de conexión a la base de datos.'
            ]);
            exit;
        }

        return $this->conexion;
    }
}
