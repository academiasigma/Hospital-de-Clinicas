<?php

require_once __DIR__ . '/Recurso.php';

// Vehículo de la flota del hospital (Ambulancia o Auto).
class Vehiculo extends Recurso {
    protected string $tabla = 'vehiculo';
    protected string $clavePrimaria = 'id_vehiculo';
    protected array $campos = ['tipo_vehiculo', 'matricula', 'estado'];

    public function validar(array $datos): array {
        $errores = [];
        if (!in_array($datos['tipo_vehiculo'] ?? null, ['Ambulancia', 'Auto'], true)) {
            $errores[] = "El tipo de vehículo debe ser 'Ambulancia' o 'Auto'.";
        }
        if (!$this->textoValido($datos['matricula'] ?? null, 20)) {
            $errores[] = 'La matrícula es obligatoria (máx. 20 caracteres).';
        }
        if (!in_array($datos['estado'] ?? null, ['Disponible', 'En Traslado', 'Mantenimiento', 'Fuera de Servicio'], true)) {
            $errores[] = 'El estado del vehículo no es válido.';
        }
        return $errores;
    }

    // Permite filtrar la flota por ?estado= y ?tipo_vehiculo=.
    public function listar(array $filtros = []): array {
        $sql = "SELECT * FROM vehiculo WHERE 1 = 1";
        $parametros = [];
        if (!empty($filtros['estado'])) {
            $sql .= " AND estado = :estado";
            $parametros['estado'] = $filtros['estado'];
        }
        if (!empty($filtros['tipo_vehiculo'])) {
            $sql .= " AND tipo_vehiculo = :tipo_vehiculo";
            $parametros['tipo_vehiculo'] = $filtros['tipo_vehiculo'];
        }
        $sql .= " ORDER BY id_vehiculo ASC";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll();
    }
}
