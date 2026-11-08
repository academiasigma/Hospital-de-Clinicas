<?php

require_once __DIR__ . '/Recurso.php';

/**
 * Categoría institucional de documentos clínicos y encuestas de satisfacción.
 * Hereda el CRUD preparado de Recurso y mantiene listar() para compatibilidad con el portal.
 */
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

    // Retorna el listado completo de las categorías ordenadas alfabéticamente.
    public function listar(array $filtros = []): array {
        $sql = "SELECT id_categoria, nombre FROM {$this->tabla} ORDER BY nombre ASC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
