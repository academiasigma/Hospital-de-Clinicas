<?php

require_once __DIR__ . '/Recurso.php';

// modelo de enfermero
class Enfermero extends Recurso {
    protected string $tabla = 'enfermero';
    protected string $clavePrimaria = 'id_enfermero';
    protected array $campos = ['nombre', 'telefono', 'matricula_profesional'];

    public function validar(array $datos): array {
        $errores = [];
        if (!$this->textoValido($datos['nombre'] ?? null, 100)) {
            $errores[] = 'El nombre es obligatorio (máx. 100 caracteres).';
        }
        if (!$this->telefonoValido($datos['telefono'] ?? null)) {
            $errores[] = 'El teléfono es obligatorio y solo admite números, +, espacios y guiones.';
        }
        if (!$this->textoValido($datos['matricula_profesional'] ?? null, 50)) {
            $errores[] = 'La matrícula profesional es obligatoria (máx. 50 caracteres).';
        }
        return $errores;
    }
}
