<?php

require_once __DIR__ . '/../config/Database.php';

class Categoria {
    private ?PDO $conexion;
    private string $tabla = 'categoria';

    public function __construct() {
        $db = new Database();
        $this->conexion = $db->conectar();
    }

    
     //Retorna el listado completo de las categorías disponibles.
    public function listar(): array {
        $sql = "SELECT id_categoria, nombre FROM {$this->tabla} ORDER BY nombre ASC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
