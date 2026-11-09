<?php

class Database {
    // datos de conexion a la base de datos
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

    // conecta con PDO probando primero el servidor y sino local
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

        // guardamos el error en el log para no mostrar datos de conexion
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
