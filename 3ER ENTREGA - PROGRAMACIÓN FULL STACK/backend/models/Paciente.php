<?php

require_once __DIR__ . '/Recurso.php';

// modelo de paciente para los traslados
class Paciente extends Recurso {
    protected string $tabla = 'paciente';
    protected string $clavePrimaria = 'id_paciente';
    protected array $campos = [
        'documento', 'nombre', 'telefono', 'fecha_de_nacimiento',
        'requiere_oxigeno', 'requiere_camilla', 'requiere_aislamiento', 'se_maneja_por_sus_medios'
    ];

    public function validar(array $datos): array {
        $errores = [];
        if (!isset($datos['documento']) || preg_match('/^[0-9]{6,12}$/', trim((string)$datos['documento'])) !== 1) {
            $errores[] = 'El documento (C.I.) es obligatorio y debe tener entre 6 y 12 dígitos, sin puntos ni guiones.';
        }
        if (!$this->textoValido($datos['nombre'] ?? null, 100)) {
            $errores[] = 'El nombre del paciente es obligatorio (máx. 100 caracteres).';
        }
        if (!empty($datos['telefono']) && !$this->telefonoValido($datos['telefono'])) {
            $errores[] = 'El teléfono solo admite números, +, espacios y guiones.';
        }
        $fecha = $datos['fecha_de_nacimiento'] ?? '';
        $fechaObj = DateTime::createFromFormat('Y-m-d', is_string($fecha) ? $fecha : '');
        if (!$fechaObj || $fechaObj->format('Y-m-d') !== $fecha || $fechaObj > new DateTime('today')) {
            $errores[] = 'La fecha de nacimiento es obligatoria, con formato AAAA-MM-DD y no puede ser futura.';
        }
        return $errores;
    }

    // busca por cedula si se pasa el parametro documento
    public function listar(array $filtros = []): array {
        if (!empty($filtros['documento'])) {
            $sql = "SELECT * FROM paciente WHERE documento = :documento";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindValue(':documento', trim((string)$filtros['documento']), PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll();
        }
        return parent::listar($filtros);
    }

    public function obtenerPorDocumento(string $documento): ?array {
        $sql = "SELECT * FROM paciente WHERE documento = :documento LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':documento', $documento, PDO::PARAM_STR);
        $stmt->execute();
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    // si el paciente ya esta registrado con esa cedula actualiza sus datos, sino lo crea
    public function crear(array $datos): ?int {
        $datos['documento'] = trim((string)($datos['documento'] ?? ''));
        foreach (['requiere_oxigeno', 'requiere_camilla', 'requiere_aislamiento'] as $campo) {
            $datos[$campo] = empty($datos[$campo]) ? 0 : 1;
        }
        // por defecto el paciente se maneja por sus medios
        $datos['se_maneja_por_sus_medios'] = (!isset($datos['se_maneja_por_sus_medios']) || $datos['se_maneja_por_sus_medios']) ? 1 : 0;

        $existente = $this->obtenerPorDocumento($datos['documento']);
        if ($existente !== null) {
            $id = (int)$existente['id_paciente'];
            return $this->actualizar($id, $datos) ? $id : null;
        }
        return parent::crear($datos);
    }

    public function actualizar(int $id, array $datos): bool {
        foreach (['requiere_oxigeno', 'requiere_camilla', 'requiere_aislamiento'] as $campo) {
            $datos[$campo] = empty($datos[$campo]) ? 0 : 1;
        }
        $datos['se_maneja_por_sus_medios'] = (!isset($datos['se_maneja_por_sus_medios']) || $datos['se_maneja_por_sus_medios']) ? 1 : 0;
        return parent::actualizar($id, $datos);
    }
}
