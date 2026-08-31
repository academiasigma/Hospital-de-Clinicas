<?php
class Database {
    private string $host = 'localhost';
    private string $db_name = 'sigsm_db';
    private string $username = 'root';
    private string $password = '';
    private ?PDO $conexion = null;

    // Obtiene y retorna la instancia activa de la conexión PDO.
    public function conectar(): ?PDO {
        $this->conexion = null;

        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
            $opciones = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            $this->conexion = new PDO($dsn, $this->username, $this->password, $opciones);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status'  => 'error',
                'mensaje' => 'Error de conexión a la base de datos: ' . $e->getMessage()
            ]);
            exit;
        }

        return $this->conexion;
    }
}
