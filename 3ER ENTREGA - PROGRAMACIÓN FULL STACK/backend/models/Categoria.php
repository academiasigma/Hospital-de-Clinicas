<?php

require_once __DIR__ . '/Recurso.php';

// modelo para las categorias de documentos y encuestas
class Categoria extends Recurso {
    protected string $tabla = 'categoria';
    protected string $clavePrimaria = 'id_categoria';
    protected array $campos = ['nombre'];

    public function validar(array $datos): array {
        $errores = [];
        if (!$this->textoValido($datos['nombre'] ?? null, 100)) {
            $errores[] = 'El nombre de la categoría es obligatorio (máx. 100 caracteres).';
        }
        return $errores;
    }

    // devuelve todas las categorias ordenadas por nombre
    public function listar(array $filtros = []): array {
        $sql = "SELECT id_categoria, nombre FROM {$this->tabla} ORDER BY nombre ASC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
