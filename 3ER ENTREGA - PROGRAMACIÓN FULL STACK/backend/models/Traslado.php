<?php

require_once __DIR__ . '/../config/Database.php';

/**
 * Traslado de pacientes u objetos (módulo Ambulancias).
 * Concentra las restricciones no estructurales (RNE01 a RNE07) en la capa de negocio y
 * accede a los datos solo con consultas preparadas PDO.
 *
 * Flujo de estados: Pendiente -> En Curso -> Retornando -> Finalizado (o Cancelado en cualquier punto activo).
 * Una división del hospital crea la solicitud (Pendiente, sin recursos); el área de transporte
 * asigna vehículo, chofer y enfermero editándola, y luego hace el seguimiento del estado.
 */
class Traslado {
    private ?PDO $conexion;

    /** Errores de validación o de regla de negocio de la última operación. */
    public array $errores = [];
    /** Código HTTP sugerido para el error: 400 (datos), 404 (no existe) o 409 (conflicto). */
    public int $codigoError = 400;

    private const ESTADOS_ACTIVOS = ['Pendiente', 'En Curso', 'Retornando'];
    private const TRANSICIONES = [
        'Pendiente'  => ['En Curso', 'Cancelado'],
        'En Curso'   => ['Retornando', 'Cancelado'],
        'Retornando' => ['Finalizado', 'Cancelado'],
        'Finalizado' => [],
        'Cancelado'  => [],
    ];
    private const CAMPOS = [
        'fecha', 'hora_salida', 'hora_estimada_llegada', 'hora_efectiva_llegada', 'hora_retorno',
        'sector_origen', 'piso_origen', 'habitacion_origen',
        'sector_destino', 'piso_destino', 'habitacion_destino', 'direccion_destino',
        'prioridad', 'observaciones', 'division_solicitante', 'motivo_procedimiento',
        'responsable_recepcion', 'limite_horario',
        'id_vehiculo', 'id_chofer', 'id_enfermero', 'id_paciente', 'id_objeto'
    ];
    private const OBLIGATORIOS = [
        'fecha', 'hora_salida', 'hora_estimada_llegada', 'sector_origen', 'piso_origen',
        'sector_destino', 'piso_destino', 'direccion_destino', 'division_solicitante',
        'motivo_procedimiento', 'responsable_recepcion'
    ];
    private const HORAS = ['hora_salida', 'hora_estimada_llegada', 'hora_efectiva_llegada', 'hora_retorno', 'limite_horario'];
    // RNE07: departamentos del interior del país (destinos prohibidos). Montevideo y Canelones son zona metropolitana.
    private const INTERIOR = [
        'artigas', 'cerro largo', 'colonia', 'durazno', 'flores', 'florida', 'lavalleja', 'maldonado',
        'paysandu', 'rio negro', 'rivera', 'rocha', 'salto', 'soriano', 'tacuarembo', 'treinta y tres'
    ];

    private const SELECT_BASE = "SELECT t.*,
            p.nombre AS paciente_nombre, p.documento AS paciente_documento,
            o.nombre AS objeto_nombre, o.tipo_objeto,
            v.matricula AS vehiculo_matricula, v.tipo_vehiculo,
            c.nombre AS chofer_nombre, e.nombre AS enfermero_nombre
        FROM traslado t
        LEFT JOIN paciente p ON p.id_paciente = t.id_paciente
        LEFT JOIN objeto o ON o.id_objeto = t.id_objeto
        LEFT JOIN vehiculo v ON v.id_vehiculo = t.id_vehiculo
        LEFT JOIN chofer c ON c.id_chofer = t.id_chofer
        LEFT JOIN enfermero e ON e.id_enfermero = t.id_enfermero";

    public function __construct() {
        $db = new Database();
        $this->conexion = $db->conectar();
    }

    // ---------------------------------------------------------------- Consultas

    /**
     * Historial de traslados. Filtros: id_paciente, documento, id_vehiculo, id_chofer, matricula,
     * estado, fecha_desde, fecha_hasta. $idUsuario limita a los traslados creados por ese usuario.
     */
    public function listar(array $filtros = [], ?int $idUsuario = null): array {
        $sql = self::SELECT_BASE . " WHERE 1 = 1";
        $p = [];

        if (!empty($filtros['id_paciente']) && ctype_digit((string)$filtros['id_paciente'])) {
            $sql .= " AND t.id_paciente = :id_paciente";
            $p['id_paciente'] = (int)$filtros['id_paciente'];
        }
        if (!empty($filtros['documento'])) {
            $sql .= " AND p.documento = :documento";
            $p['documento'] = trim((string)$filtros['documento']);
        }
        if (!empty($filtros['id_vehiculo']) && ctype_digit((string)$filtros['id_vehiculo'])) {
            $sql .= " AND t.id_vehiculo = :id_vehiculo";
            $p['id_vehiculo'] = (int)$filtros['id_vehiculo'];
        }
        if (!empty($filtros['id_chofer']) && ctype_digit((string)$filtros['id_chofer'])) {
            $sql .= " AND t.id_chofer = :id_chofer";
            $p['id_chofer'] = (int)$filtros['id_chofer'];
        }
        if (!empty($filtros['matricula'])) {
            $sql .= " AND v.matricula = :matricula";
            $p['matricula'] = trim((string)$filtros['matricula']);
        }
        if (!empty($filtros['estado']) && isset(self::TRANSICIONES[$filtros['estado']])) {
            $sql .= " AND t.estado = :estado";
            $p['estado'] = $filtros['estado'];
        }
        if (!empty($filtros['fecha_desde']) && $this->fechaValida($filtros['fecha_desde'])) {
            $sql .= " AND t.fecha >= :fecha_desde";
            $p['fecha_desde'] = $filtros['fecha_desde'];
        }
        if (!empty($filtros['fecha_hasta']) && $this->fechaValida($filtros['fecha_hasta'])) {
            $sql .= " AND t.fecha <= :fecha_hasta";
            $p['fecha_hasta'] = $filtros['fecha_hasta'];
        }
        if ($idUsuario !== null) {
            $sql .= " AND t.id_usuario = :id_usuario";
            $p['id_usuario'] = $idUsuario;
        }
        $sql .= " ORDER BY t.fecha DESC, t.hora_salida DESC";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($p);
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): ?array {
        $stmt = $this->conexion->prepare(self::SELECT_BASE . " WHERE t.id_traslado = :id LIMIT 1");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    // ---------------------------------------------------------------- Escritura

    /**
     * Registra un traslado. Si $soloSolicitud es true (usuario de una división) se ignoran
     * vehículo, chofer y enfermero: los asigna después el área de transporte.
     */
    public function crear(array $datos, int $idUsuario, bool $soloSolicitud = false): ?int {
        $datos = $this->normalizar($datos);
        if ($soloSolicitud) {
            $datos['id_vehiculo'] = $datos['id_chofer'] = $datos['id_enfermero'] = null;
        }

        try {
            $this->conexion->beginTransaction();
            $this->bloquearRecursos($datos);

            if (!$this->validar($datos, null)) {
                $this->conexion->rollBack();
                return null;
            }

            $columnas = implode(', ', self::CAMPOS) . ', id_usuario';
            $marcas = implode(', ', array_map(fn($c) => ':' . $c, self::CAMPOS)) . ', :id_usuario';
            $stmt = $this->conexion->prepare("INSERT INTO traslado ($columnas) VALUES ($marcas)");
            $parametros = array_intersect_key($datos, array_flip(self::CAMPOS));
            $parametros['id_usuario'] = $idUsuario;
            $stmt->execute($parametros);

            $id = (int)$this->conexion->lastInsertId();
            $this->conexion->commit();
            return $id;
        } catch (PDOException $e) {
            return $this->falloInterno($e);
        }
    }

    /**
     * Edición completa (incluye asignar vehículo, chofer y enfermero). Solo se permite mientras
     * el traslado está Pendiente; una vez iniciado se gestiona con cambiarEstado().
     * Los horarios reales (llegada efectiva y retorno) no se editan por aquí.
     */
    public function actualizar(int $id, array $datos): bool {
        $actual = $this->obtenerPorId($id);
        if ($actual === null) {
            return $this->fallar(404, 'Traslado no encontrado.');
        }
        if ($actual['estado'] !== 'Pendiente') {
            return $this->fallar(409, "Solo se puede editar un traslado Pendiente. Estado actual: {$actual['estado']}.");
        }

        $datos = $this->normalizar($datos);
        $datos['hora_efectiva_llegada'] = $actual['hora_efectiva_llegada'];
        $datos['hora_retorno'] = $actual['hora_retorno'];

        try {
            $this->conexion->beginTransaction();
            $this->bloquearRecursos($datos);

            if (!$this->validar($datos, $id)) {
                $this->conexion->rollBack();
                return false;
            }

            $asignaciones = implode(', ', array_map(fn($c) => "$c = :$c", self::CAMPOS));
            $stmt = $this->conexion->prepare("UPDATE traslado SET $asignaciones WHERE id_traslado = :id_traslado");
            $parametros = array_intersect_key($datos, array_flip(self::CAMPOS));
            $parametros['id_traslado'] = $id;
            $stmt->execute($parametros);

            $this->conexion->commit();
            return true;
        } catch (PDOException $e) {
            $this->falloInterno($e);
            return false;
        }
    }

    /**
     * Seguimiento del estado. Valida la transición y registra los horarios reales:
     * al pasar a Retornando se guarda la llegada efectiva y al Finalizar la hora de retorno
     * (si no se informan, se usa la hora actual). Sincroniza el estado del vehículo.
     */
    public function cambiarEstado(int $id, string $nuevoEstado, ?string $horaEfectiva = null, ?string $horaRetorno = null): bool {
        $t = $this->obtenerPorId($id);
        if ($t === null) {
            return $this->fallar(404, 'Traslado no encontrado.');
        }
        if (!isset(self::TRANSICIONES[$nuevoEstado])) {
            return $this->fallar(400, 'El estado indicado no es válido.');
        }
        if (!in_array($nuevoEstado, self::TRANSICIONES[$t['estado']], true)) {
            return $this->fallar(409, "Transición no permitida: {$t['estado']} → $nuevoEstado.");
        }

        $efectiva = $t['hora_efectiva_llegada'];
        $retorno = $t['hora_retorno'];

        if ($nuevoEstado === 'En Curso' && ($t['id_vehiculo'] === null || $t['id_chofer'] === null)) {
            return $this->fallar(409, 'Antes de iniciar el traslado debe asignarse vehículo y chofer.');
        }
        if ($nuevoEstado === 'Retornando') {
            $efectiva = $this->normalizarHora($horaEfectiva) ?? date('H:i:s');
            if ($efectiva <= $t['hora_salida']) {
                return $this->fallar(400, 'La hora de llegada efectiva debe ser posterior a la hora de salida.');
            }
        }
        if ($nuevoEstado === 'Finalizado') {
            $retorno = $this->normalizarHora($horaRetorno) ?? date('H:i:s');
            if ($efectiva !== null && $retorno < $efectiva) {
                return $this->fallar(400, 'La hora de retorno no puede ser anterior a la llegada efectiva.');
            }
        }

        try {
            $this->conexion->beginTransaction();

            $sql = "UPDATE traslado SET estado = :estado, hora_efectiva_llegada = :efectiva, hora_retorno = :retorno
                    WHERE id_traslado = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute(['estado' => $nuevoEstado, 'efectiva' => $efectiva, 'retorno' => $retorno, 'id' => $id]);

            $this->sincronizarVehiculo($t['id_vehiculo'] !== null ? (int)$t['id_vehiculo'] : null, $nuevoEstado);

            $this->conexion->commit();
            return true;
        } catch (PDOException $e) {
            $this->falloInterno($e);
            return false;
        }
    }

    // Cancelación lógica (DELETE): el traslado queda en el historial con estado Cancelado.
    public function cancelar(int $id): bool {
        return $this->cambiarEstado($id, 'Cancelado');
    }

    // ---------------------------------------------------------------- Reglas de negocio

    /**
     * Aplica las restricciones no estructurales. Devuelve true si el traslado es válido;
     * si no, deja los mensajes en $errores y el código HTTP en $codigoError.
     */
    private function validar(array &$d, ?int $idActual): bool {
        $this->errores = [];
        $conflicto = false;

        foreach (self::OBLIGATORIOS as $campo) {
            if ($d[$campo] === null || $d[$campo] === '') {
                $this->errores[] = "El campo $campo es obligatorio.";
            }
        }
        if ($d['fecha'] !== null && !$this->fechaValida($d['fecha'])) {
            $this->errores[] = 'La fecha debe tener formato AAAA-MM-DD.';
        }
        foreach (self::HORAS as $campo) {
            if ($d[$campo] !== null && !$this->horaValida($d[$campo])) {
                $this->errores[] = "El campo $campo debe tener formato HH:MM.";
            }
        }
        if (!in_array($d['prioridad'], ['Alta', 'Media', 'Baja'], true)) {
            $this->errores[] = 'La prioridad debe ser Alta, Media o Baja.';
        }
        if (!empty($this->errores)) {
            $this->codigoError = 400;
            return false;
        }

        // RNE01: coherencia cronológica
        if ($d['hora_estimada_llegada'] <= $d['hora_salida']) {
            $this->errores[] = 'La hora estimada de llegada debe ser posterior a la hora de salida.';
        }

        // RNE07: destino restringido a la zona metropolitana
        $destino = $this->sinAcentos($d['direccion_destino'] . ' ' . $d['sector_destino']);
        foreach (self::INTERIOR as $depto) {
            if (preg_match('/\b' . preg_quote($depto, '/') . '\b/', $destino)) {
                $this->errores[] = 'No se permiten traslados al interior del país (zona metropolitana únicamente).';
                break;
            }
        }

        // RNE03: paciente u objeto, nunca ambos ni ninguno
        $paciente = $objeto = null;
        if (($d['id_paciente'] === null) === ($d['id_objeto'] === null)) {
            $this->errores[] = 'El traslado debe asociarse a un paciente o a un objeto, nunca a ambos.';
        } elseif ($d['id_paciente'] !== null) {
            $paciente = $this->buscar('paciente', 'id_paciente', $d['id_paciente']);
            if ($paciente === null) {
                $this->errores[] = 'El paciente indicado no existe.';
            }
        } else {
            $objeto = $this->buscar('objeto', 'id_objeto', $d['id_objeto']);
            if ($objeto === null) {
                $this->errores[] = 'El objeto indicado no existe.';
            }
        }

        // Tipo de vehículo exigido según la carga (RNE05, RNE06 y requisitos clínicos)
        $exigeAmbulancia = false;
        if ($paciente !== null) {
            $exigeAmbulancia = !$paciente['se_maneja_por_sus_medios'] || $paciente['requiere_oxigeno']
                || $paciente['requiere_camilla'] || $paciente['requiere_aislamiento'];
        }
        if ($objeto !== null) {
            $d['id_enfermero'] = null; // RNE03/RNE05: un objeto no lleva enfermero acompañante
            if (in_array($objeto['tipo_objeto'], ['organo', 'muestra_biologica'], true)) {
                $exigeAmbulancia = true;
            }
            if ($objeto['tipo_objeto'] === 'organo') {
                $d['prioridad'] = 'Alta'; // RNE05: prioridad máxima
                if ($d['limite_horario'] === null) {
                    $this->errores[] = 'El traslado de un órgano exige registrar la hora límite.';
                }
            }
        }

        // Vehículo (RNE04, RNE06)
        if ($d['id_vehiculo'] !== null) {
            $vehiculo = $this->buscar('vehiculo', 'id_vehiculo', $d['id_vehiculo']);
            if ($vehiculo === null) {
                $this->errores[] = 'El vehículo indicado no existe.';
            } else {
                if (in_array($vehiculo['estado'], ['Mantenimiento', 'Fuera de Servicio'], true)) {
                    $this->errores[] = "El vehículo {$vehiculo['matricula']} se encuentra en {$vehiculo['estado']} y no puede asignarse.";
                    $conflicto = true;
                }
                if ($exigeAmbulancia && $vehiculo['tipo_vehiculo'] !== 'Ambulancia') {
                    $this->errores[] = 'Por las condiciones clínicas o de la carga, este traslado requiere una Ambulancia.';
                    $conflicto = true;
                }
            }
        }

        // Chofer y enfermero
        if ($d['id_chofer'] !== null && $this->buscar('chofer', 'id_chofer', $d['id_chofer']) === null) {
            $this->errores[] = 'El chofer indicado no existe.';
        }
        if ($d['id_enfermero'] !== null && $this->buscar('enfermero', 'id_enfermero', $d['id_enfermero']) === null) {
            $this->errores[] = 'El enfermero indicado no existe.';
        }
        // RNE03: enfermero obligatorio para pacientes con prioridad Alta (se exige al asignar recursos)
        if ($paciente !== null && $d['prioridad'] === 'Alta' && $d['id_vehiculo'] !== null && $d['id_enfermero'] === null) {
            $this->errores[] = 'Un traslado de paciente con prioridad Alta requiere enfermero acompañante.';
        }

        // RNE02: exclusividad temporal de vehículo, chofer y enfermero
        if (empty($this->errores) || $conflicto) {
            $recursos = ['id_vehiculo' => 'vehículo', 'id_chofer' => 'chofer', 'id_enfermero' => 'enfermero'];
            foreach ($recursos as $columna => $nombre) {
                if ($d[$columna] !== null && $this->haySolapamiento($columna, (int)$d[$columna], $d, $idActual)) {
                    $this->errores[] = "El $nombre seleccionado ya está asignado a otro traslado activo en ese horario.";
                    $conflicto = true;
                }
            }
        }

        if (!empty($this->errores)) {
            $this->codigoError = $conflicto ? 409 : 400;
            return false;
        }
        return true;
    }

    // RNE02: ¿hay otro traslado activo del mismo recurso cuyo intervalo [salida, retorno/llegada estimada] se superpone?
    private function haySolapamiento(string $columna, int $valor, array $d, ?int $idActual): bool {
        if (!in_array($columna, ['id_vehiculo', 'id_chofer', 'id_enfermero'], true)) {
            return false; // lista blanca: el nombre de columna nunca viene del usuario
        }
        $sql = "SELECT COUNT(*) FROM traslado
                WHERE $columna = :valor AND fecha = :fecha
                  AND estado IN ('Pendiente', 'En Curso', 'Retornando')
                  AND id_traslado <> :id
                  AND hora_salida < :fin
                  AND COALESCE(hora_retorno, hora_estimada_llegada) > :inicio";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            'valor'  => $valor,
            'fecha'  => $d['fecha'],
            'id'     => $idActual ?? 0,
            'fin'    => $d['hora_retorno'] ?? $d['hora_estimada_llegada'],
            'inicio' => $d['hora_salida'],
        ]);
        return (int)$stmt->fetchColumn() > 0;
    }

    // Bloquea las filas de vehículo y chofer para que dos solicitudes simultáneas no se asignen el mismo recurso.
    private function bloquearRecursos(array $d): void {
        foreach (['vehiculo' => 'id_vehiculo', 'chofer' => 'id_chofer', 'enfermero' => 'id_enfermero'] as $tabla => $columna) {
            if ($d[$columna] !== null) {
                $stmt = $this->conexion->prepare("SELECT $columna FROM $tabla WHERE $columna = :id FOR UPDATE");
                $stmt->execute(['id' => $d[$columna]]);
            }
        }
    }

    /**
     * El vehículo pasa a 'En Traslado' al iniciar y vuelve a 'Disponible' al finalizar o cancelar,
     * salvo que esté en Mantenimiento/Fuera de Servicio o tenga otro traslado en curso.
     */
    private function sincronizarVehiculo(?int $idVehiculo, string $nuevoEstado): void {
        if ($idVehiculo === null) {
            return;
        }
        if ($nuevoEstado === 'En Curso') {
            $sql = "UPDATE vehiculo SET estado = 'En Traslado' WHERE id_vehiculo = :id AND estado = 'Disponible'";
        } elseif ($nuevoEstado === 'Finalizado' || $nuevoEstado === 'Cancelado') {
            $sql = "UPDATE vehiculo SET estado = 'Disponible'
                    WHERE id_vehiculo = :id AND estado = 'En Traslado'
                      AND NOT EXISTS (SELECT 1 FROM traslado WHERE id_vehiculo = :id2 AND estado IN ('En Curso', 'Retornando'))";
        } else {
            return;
        }
        $stmt = $this->conexion->prepare($sql);
        $parametros = ['id' => $idVehiculo];
        if (str_contains($sql, ':id2')) {
            $parametros['id2'] = $idVehiculo;
        }
        $stmt->execute($parametros);
    }

    // ---------------------------------------------------------------- Auxiliares

    // Lista blanca de campos; '' -> null; ids a int; horas HH:MM -> HH:MM:SS.
    private function normalizar(array $datos): array {
        $limpio = [];
        foreach (self::CAMPOS as $campo) {
            $valor = $datos[$campo] ?? null;
            if (is_string($valor)) {
                $valor = trim($valor);
                $valor = ($valor === '') ? null : $valor;
            }
            if (str_starts_with($campo, 'id_') && $valor !== null) {
                $valor = ctype_digit((string)$valor) ? (int)$valor : 0;
            }
            if (in_array($campo, self::HORAS, true)) {
                $valor = $this->normalizarHora($valor);
            }
            $limpio[$campo] = $valor;
        }
        $limpio['prioridad'] = $limpio['prioridad'] ?? 'Media';
        return $limpio;
    }

    private function normalizarHora(?string $hora): ?string {
        if ($hora === null || $hora === '') {
            return null;
        }
        return preg_match('/^\d{2}:\d{2}$/', $hora) ? $hora . ':00' : $hora;
    }

    private function horaValida(string $hora): bool {
        return preg_match('/^([01]\d|2[0-3]):[0-5]\d:[0-5]\d$/', $hora) === 1;
    }

    private function fechaValida($fecha): bool {
        if (!is_string($fecha)) {
            return false;
        }
        $obj = DateTime::createFromFormat('Y-m-d', $fecha);
        return $obj && $obj->format('Y-m-d') === $fecha;
    }

    private function sinAcentos(string $texto): string {
        return strtr(mb_strtolower($texto, 'UTF-8'), ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n']);
    }

    // Búsqueda por clave primaria en tablas de referencia (los nombres de tabla/columna son constantes del código).
    private function buscar(string $tabla, string $clave, int $id): ?array {
        $stmt = $this->conexion->prepare("SELECT * FROM $tabla WHERE $clave = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    private function fallar(int $codigo, string $mensaje): bool {
        $this->codigoError = $codigo;
        $this->errores = [$mensaje];
        return false;
    }

    private function falloInterno(PDOException $e): ?int {
        if ($this->conexion->inTransaction()) {
            $this->conexion->rollBack();
        }
        error_log('Error en Traslado: ' . $e->getMessage());
        $this->codigoError = 500;
        $this->errores = ['Error en el servidor al procesar el traslado.'];
        return null;
    }
}
