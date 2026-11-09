<?php

require_once __DIR__ . '/../config/Database.php';

// clase base para chofer, enfermero, vehiculo y paciente
abstract class Recurso {
    protected ?PDO $conexion;
    protected string $tabla;
    protected string $clavePrimaria;
    protected array $campos = [];

    // guarda si hubo error al insertar o borrar
    public string $ultimoError = '';

    public function __construct() {
        $db = new Database();
        $this->conexion = $db->conectar();
    }

    // cada clase valida sus propios datos
    abstract public function validar(array $datos): array;

    // devuelve todos los registros de la tabla
    public function listar(array $filtros = []): array {
        $sql = "SELECT * FROM {$this->tabla} ORDER BY {$this->clavePrimaria} ASC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): ?array {
        $sql = "SELECT * FROM {$this->tabla} WHERE {$this->clavePrimaria} = :id LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    // inserta un nuevo registro y devuelve el id
    public function crear(array $datos): ?int {
        $this->ultimoError = '';
        $columnas = implode(', ', $this->campos);
        $marcas = implode(', ', array_map(fn($c) => ':' . $c, $this->campos));
        $sql = "INSERT INTO {$this->tabla} ($columnas) VALUES ($marcas)";

        try {
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute($this->limpiar($datos));
            return (int)$this->conexion->lastInsertId();
        } catch (PDOException $e) {
            $this->registrarError($e);
            return null;
        }
    }

    public function actualizar(int $id, array $datos): bool {
        $this->ultimoError = '';
        $asignaciones = implode(', ', array_map(fn($c) => "$c = :$c", $this->campos));
        $sql = "UPDATE {$this->tabla} SET $asignaciones WHERE {$this->clavePrimaria} = :id_registro";

        try {
            $stmt = $this->conexion->prepare($sql);
            $parametros = $this->limpiar($datos);
            $parametros['id_registro'] = $id;
            $stmt->execute($parametros);
            return true;
        } catch (PDOException $e) {
            $this->registrarError($e);
            return false;
        }
    }

    // elimina un registro por su id
    public function eliminar(int $id): bool {
        $this->ultimoError = '';
        $sql = "DELETE FROM {$this->tabla} WHERE {$this->clavePrimaria} = :id";

        try {
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            $this->registrarError($e);
            return false;
        }
    }

    // limpia los espacios de los campos
    protected function limpiar(array $datos): array {
        $limpio = [];
        foreach ($this->campos as $campo) {
            $valor = $datos[$campo] ?? null;
            if (is_string($valor)) {
                $valor = trim($valor);
                $valor = ($valor === '') ? null : $valor;
            }
            $limpio[$campo] = $valor;
        }
        return $limpio;
    }

    // revisa el codigo de error de mysql para ver si esta repetido o en uso
    protected function registrarError(PDOException $e): void {
        $codigoMotor = $e->errorInfo[1] ?? 0;
        if ($codigoMotor === 1062) {
            $this->ultimoError = 'duplicado';
        } elseif ($codigoMotor === 1451) {
            $this->ultimoError = 'referenciado';
        } else {
            $this->ultimoError = 'error';
            error_log('Error en ' . static::class . ': ' . $e->getMessage());
        }
    }

    // funciones para validar textos y telefonos
    protected function textoValido(?string $valor, int $max): bool {
        return is_string($valor) && trim($valor) !== '' && mb_strlen(trim($valor)) <= $max;
    }

    protected function telefonoValido(?string $valor): bool {
        return is_string($valor) && preg_match('/^[0-9+\s-]{7,20}$/', trim($valor)) === 1;
    }
}
