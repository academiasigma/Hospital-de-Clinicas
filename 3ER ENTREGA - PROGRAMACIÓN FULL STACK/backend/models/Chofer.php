<?php

require_once __DIR__ . '/Recurso.php';

// modelo de chofer
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
}
