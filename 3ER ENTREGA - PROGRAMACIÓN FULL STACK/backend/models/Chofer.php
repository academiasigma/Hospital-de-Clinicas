<?php

require_once __DIR__ . '/Recurso.php';

// Chofer del hospital: puede manejar toda la flota (ambulancias y autos).
class Chofer extends Recurso {
    protected string $tabla = 'chofer';
    protected string $clavePrimaria = 'id_chofer';
    protected array $campos = ['nombre', 'telefono', 'licencia'];

    public function validar(array $datos): array {
        $errores = [];
        if (!$this->textoValido($datos['nombre'] ?? null, 100)) {
            $errores[] = 'El nombre es obligatorio (máx. 100 caracteres).';
        }
        if (!$this->telefonoValido($datos['telefono'] ?? null)) {
            $errores[] = 'El teléfono es obligatorio y solo admite números, +, espacios y guiones.';
        }
        if (!$this->textoValido($datos['licencia'] ?? null, 50)) {
            $errores[] = 'La licencia de conducir es obligatoria (máx. 50 caracteres).';
        }
        return $errores;
    }

    public function obtenerPorLicencia(string $licencia): ?array {
        $stmt = $this->bd->prepare("SELECT * FROM chofer WHERE UPPER(TRIM(licencia)) = UPPER(TRIM(:lic)) LIMIT 1");
        $stmt->execute(['lic' => $licencia]);
        $fila = $stmt->fetch();
        return $fila ?: null;
    }
}
