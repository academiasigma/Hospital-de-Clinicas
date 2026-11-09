<?php

require_once __DIR__ . '/../config/Database.php';

/**
 * Encuesta de satisfacción anónima vinculada a una categoría clínica.
 * Implementa las restricciones RNE08, RNE09 y RNE10 en guardarResolucion()
 * y expone el resumen estadístico básico para el panel administrativo.
 */
class Encuesta {
    private ?PDO $conexion;
    private string $tabla = 'encuesta';

    /** Mensaje de error de validación (RNE08 / RNE09 / RNE10) de la última resolución. */
    public string $ultimoError = '';
    public int $codigoError = 400;

    public function __construct() {
        $db = new Database();
        $this->conexion = $db->conectar();
    }

    // Obtiene la encuesta activa de una categoría específica, con sus preguntas y opciones.
    public function obtenerActivaPorCategoria(int $id_categoria): ?array {
        $sql = "SELECT id_encuesta, comentarios, id_categoria 
                FROM {$this->tabla} 
                WHERE id_categoria = :id_categoria AND estado = 'Activa' 
                ORDER BY id_encuesta DESC
                LIMIT 1";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id_categoria', $id_categoria, PDO::PARAM_INT);
        $stmt->execute();

        $encuesta = $stmt->fetch();
        if (!$encuesta) {
            return null;
        }

        return $this->cargarPreguntasDeEncuesta($encuesta);
    }

    // Obtiene una encuesta activa específica por su ID, con sus preguntas y opciones.
    public function obtenerActivaPorId(int $id_encuesta): ?array {
        $sql = "SELECT e.id_encuesta, e.comentarios, e.id_categoria, c.nombre AS categoria_nombre
                FROM {$this->tabla} e
                INNER JOIN categoria c ON c.id_categoria = e.id_categoria
                WHERE e.id_encuesta = :id_encuesta AND e.estado = 'Activa'
                LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':id_encuesta', $id_encuesta, PDO::PARAM_INT);
        $stmt->execute();

        $encuesta = $stmt->fetch();
        if (!$encuesta) {
            return null;
        }

        return $this->cargarPreguntasDeEncuesta($encuesta);
    }

    // Lista todas las encuestas activas (con cantidad de preguntas) para el Portal de Invitados.
    public function listarActivasPublicas(): array {
        $sql = "SELECT e.id_encuesta, e.comentarios, e.id_categoria, c.nombre AS categoria_nombre,
                       (SELECT COUNT(*) FROM pregunta p WHERE p.id_encuesta = e.id_encuesta) AS total_preguntas
                FROM {$this->tabla} e
                INNER JOIN categoria c ON c.id_categoria = e.id_categoria
                WHERE e.estado = 'Activa'
                ORDER BY e.id_encuesta DESC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function cargarPreguntasDeEncuesta(array $encuesta): array {
        $id_encuesta = (int)$encuesta['id_encuesta'];

        // Obtener preguntas de la encuesta
        $sqlPreguntas = "SELECT id_pregunta, enunciado, tipo 
                         FROM pregunta 
                         WHERE id_encuesta = :id_encuesta
                         ORDER BY id_pregunta ASC";
        $stmtPreguntas = $this->conexion->prepare($sqlPreguntas);
        $stmtPreguntas->bindParam(':id_encuesta', $id_encuesta, PDO::PARAM_INT);
        $stmtPreguntas->execute();
        $preguntas = $stmtPreguntas->fetchAll();

        // Para cada pregunta de tipo Opcion_Multiple, obtener sus opciones
        foreach ($preguntas as &$pregunta) {
            $pregunta['id_pregunta'] = (int)$pregunta['id_pregunta'];
            if ($pregunta['tipo'] === 'Opcion_Multiple') {
                $sqlOpciones = "SELECT id_opcion, texto_opcion 
                                FROM opcion_pregunta 
                                WHERE id_pregunta = :id_pregunta
                                ORDER BY id_opcion ASC";
                $stmtOpciones = $this->conexion->prepare($sqlOpciones);
                $stmtOpciones->bindParam(':id_pregunta', $pregunta['id_pregunta'], PDO::PARAM_INT);
                $stmtOpciones->execute();
                $pregunta['opciones'] = $stmtOpciones->fetchAll();
            } else {
                $pregunta['opciones'] = [];
            }
        }

        $encuesta['id_encuesta'] = $id_encuesta;
        $encuesta['preguntas'] = $preguntas;

        return $encuesta;
    }

    /**
     * Guarda de forma anónima y transaccional una resolución de encuesta con sus respuestas.
     * Valida:
     *   - RNE08: la encuesta debe existir y tener estado = 'Activa'.
     *   - RNE09: cada id_pregunta debe pertenecer a esa misma id_encuesta.
     *   - RNE10: dominio de respuesta según pregunta.tipo (Si/No -> SI/NO, Escala_1_5 -> 1..5).
     */
    public function guardarResolucion(int $id_encuesta, string $observaciones, array $respuestas): bool {
        $this->ultimoError = '';
        $this->codigoError = 400;

        // RNE08: verificar que la encuesta exista y esté Activa
        $stmtEnc = $this->conexion->prepare(
            "SELECT id_encuesta, estado FROM encuesta WHERE id_encuesta = :id LIMIT 1"
        );
        $stmtEnc->bindValue(':id', $id_encuesta, PDO::PARAM_INT);
        $stmtEnc->execute();
        $encuesta = $stmtEnc->fetch();

        if (!$encuesta) {
            $this->ultimoError = 'La encuesta indicada no existe.';
            $this->codigoError = 404;
            return false;
        }
        if ($encuesta['estado'] !== 'Activa') {
            $this->ultimoError = 'Solo se admiten respuestas para encuestas que se encuentren activas.';
            $this->codigoError = 409;
            return false;
        }

        // Cargar preguntas válidas de esta encuesta para verificar RNE09 y RNE10
        $stmtPregs = $this->conexion->prepare(
            "SELECT id_pregunta, tipo FROM pregunta WHERE id_encuesta = :id"
        );
        $stmtPregs->bindValue(':id', $id_encuesta, PDO::PARAM_INT);
        $stmtPregs->execute();
        $mapaPreguntas = [];
        foreach ($stmtPregs->fetchAll() as $p) {
            $mapaPreguntas[(int)$p['id_pregunta']] = $p['tipo'];
        }

        $respuestasLimpias = [];
        foreach ($respuestas as $resp) {
            $id_pregunta = (int)($resp['id_pregunta'] ?? 0);
            $contenido = trim((string)($resp['contenido_respuesta'] ?? ''));

            // RNE09: la pregunta debe pertenecer a la encuesta
            if (!isset($mapaPreguntas[$id_pregunta])) {
                $this->ultimoError = "La pregunta #$id_pregunta no pertenece a la encuesta seleccionada.";
                return false;
            }

            $tipo = $mapaPreguntas[$id_pregunta];

            if ($contenido === '') {
                if ($tipo === 'Texto Libre') {
                    continue;
                }
                $this->ultimoError = 'Debe responder todas las preguntas de opción o calificación.';
                return false;
            }

            // RNE10: validación de dominio según el tipo de pregunta
            if ($tipo === 'Si/No') {
                $upper = strtr(mb_strtoupper($contenido, 'UTF-8'), ['Í' => 'I']);
                if ($upper !== 'SI' && $upper !== 'NO') {
                    $this->ultimoError = "Para preguntas de tipo Sí/No el valor solo puede ser 'Sí' o 'No'.";
                    return false;
                }
                $contenido = ($upper === 'SI') ? 'Sí' : 'No';
            } elseif ($tipo === 'Escala_1_5') {
                if (!in_array($contenido, ['1', '2', '3', '4', '5'], true)) {
                    $this->ultimoError = 'La calificación en escala debe ser un número entero entre 1 y 5.';
                    return false;
                }
            } elseif ($tipo === 'Opcion_Multiple') {
                $stmtOps = $this->conexion->prepare(
                    "SELECT texto_opcion FROM opcion_pregunta WHERE id_pregunta = :id_pregunta"
                );
                $stmtOps->execute(['id_pregunta' => $id_pregunta]);
                $opcionesValidas = $stmtOps->fetchAll(PDO::FETCH_COLUMN);
                if (!in_array($contenido, $opcionesValidas, true)) {
                    $this->ultimoError = 'La opción seleccionada no pertenece a las opciones válidas de la pregunta.';
                    return false;
                }
            }

            $respuestasLimpias[] = [
                'id_pregunta' => $id_pregunta,
                'contenido'   => $contenido
            ];
        }

        if (empty($respuestasLimpias)) {
            $this->ultimoError = 'Debe responder al menos una pregunta de la encuesta.';
            return false;
        }

        try {
            $this->conexion->beginTransaction();

            $sqlResolucion = "INSERT INTO resolucion_encuesta (id_encuesta, observaciones) 
                              VALUES (:id_encuesta, :observaciones)";
            $stmtRes = $this->conexion->prepare($sqlResolucion);
            $stmtRes->bindValue(':id_encuesta', $id_encuesta, PDO::PARAM_INT);
            $stmtRes->bindValue(':observaciones', $observaciones !== '' ? $observaciones : null, PDO::PARAM_STR);
            $stmtRes->execute();

            $id_resolucion = (int)$this->conexion->lastInsertId();

            $sqlRespuesta = "INSERT INTO respuesta (contenido_respuesta, id_resolucion, id_pregunta) 
                             VALUES (:contenido, :id_resolucion, :id_pregunta)";
            $stmtResp = $this->conexion->prepare($sqlRespuesta);

            foreach ($respuestasLimpias as $item) {
                $stmtResp->bindValue(':contenido', $item['contenido'], PDO::PARAM_STR);
                $stmtResp->bindValue(':id_resolucion', $id_resolucion, PDO::PARAM_INT);
                $stmtResp->bindValue(':id_pregunta', $item['id_pregunta'], PDO::PARAM_INT);
                $stmtResp->execute();
            }

            $this->conexion->commit();
            return true;
        } catch (Exception $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            error_log('Error al guardar resolucion: ' . $e->getMessage());
            $this->ultimoError = 'Error interno al almacenar las respuestas.';
            $this->codigoError = 500;
            return false;
        }
    }

    /**
     * Obtiene el resumen de resultados de las encuestas de satisfacción (opcionalmente filtrado por categoría):
     * cantidad de resoluciones, desglose por pregunta (conteo Sí/No, promedio y distribución 1-5, textos libres)
     * y observaciones generales registradas.
     */
    public function obtenerResultados(?int $id_categoria = null): array {
        $sql = "SELECT e.id_encuesta, e.comentarios, e.estado, e.id_categoria, c.nombre AS categoria_nombre
                FROM encuesta e
                INNER JOIN categoria c ON c.id_categoria = e.id_categoria";
        if ($id_categoria !== null && $id_categoria > 0) {
            $sql .= " WHERE e.id_categoria = :id_categoria";
        }
        $sql .= " ORDER BY e.id_encuesta DESC";

        $stmt = $this->conexion->prepare($sql);
        if ($id_categoria !== null && $id_categoria > 0) {
            $stmt->bindValue(':id_categoria', $id_categoria, PDO::PARAM_INT);
        }
        $stmt->execute();
        $encuestas = $stmt->fetchAll();

        $stmtResoluciones = $this->conexion->prepare(
            "SELECT id_resolucion, fecha_hora, observaciones
             FROM resolucion_encuesta
             WHERE id_encuesta = :id_encuesta
             ORDER BY fecha_hora DESC"
        );
        $stmtPreguntas = $this->conexion->prepare(
            "SELECT id_pregunta, enunciado, tipo
             FROM pregunta
             WHERE id_encuesta = :id_encuesta
             ORDER BY id_pregunta ASC"
        );
        $stmtRespuestas = $this->conexion->prepare(
            "SELECT contenido_respuesta
             FROM respuesta
             WHERE id_pregunta = :id_pregunta
             ORDER BY id_respuesta DESC"
        );

        $resultado = [];
        foreach ($encuestas as $enc) {
            $idEnc = (int)$enc['id_encuesta'];

            $stmtResoluciones->execute(['id_encuesta' => $idEnc]);
            $resoluciones = $stmtResoluciones->fetchAll();

            $observaciones = [];
            foreach ($resoluciones as $res) {
                if ($res['observaciones'] !== null && trim($res['observaciones']) !== '') {
                    $observaciones[] = [
                        'fecha_hora' => $res['fecha_hora'],
                        'texto'      => $res['observaciones']
                    ];
                }
            }

            $stmtPreguntas->execute(['id_encuesta' => $idEnc]);
            $preguntas = $stmtPreguntas->fetchAll();
            $preguntasFormateadas = [];

            foreach ($preguntas as $preg) {
                $idPreg = (int)$preg['id_pregunta'];
                $stmtRespuestas->execute(['id_pregunta' => $idPreg]);
                $valores = $stmtRespuestas->fetchAll(PDO::FETCH_COLUMN);

                $resumenPregunta = [
                    'id_pregunta'      => $idPreg,
                    'enunciado'        => $preg['enunciado'],
                    'tipo'             => $preg['tipo'],
                    'total_respuestas' => count($valores)
                ];

                if ($preg['tipo'] === 'Si/No') {
                    $si = 0;
                    $no = 0;
                    foreach ($valores as $v) {
                        $norm = strtr(mb_strtoupper(trim((string)$v), 'UTF-8'), ['Í' => 'I']);
                        if ($norm === 'SI') {
                            $si++;
                        } elseif ($norm === 'NO') {
                            $no++;
                        }
                    }
                    $resumenPregunta['si'] = $si;
                    $resumenPregunta['no'] = $no;
                } elseif ($preg['tipo'] === 'Escala_1_5') {
                    $distribucion = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
                    $suma = 0;
                    $conteoValido = 0;
                    foreach ($valores as $v) {
                        $num = (int)$v;
                        if ($num >= 1 && $num <= 5) {
                            $distribucion[$num]++;
                            $suma += $num;
                            $conteoValido++;
                        }
                    }
                    $resumenPregunta['promedio'] = $conteoValido > 0 ? round($suma / $conteoValido, 2) : null;
                    $resumenPregunta['distribucion'] = $distribucion;
                } elseif ($preg['tipo'] === 'Opcion_Multiple') {
                    $stmtOps = $this->conexion->prepare(
                        "SELECT texto_opcion FROM opcion_pregunta WHERE id_pregunta = :id_pregunta ORDER BY id_opcion ASC"
                    );
                    $stmtOps->execute(['id_pregunta' => $idPreg]);
                    $opciones = $stmtOps->fetchAll(PDO::FETCH_COLUMN);
                    $conteoOpciones = [];
                    foreach ($opciones as $op) {
                        $conteoOpciones[$op] = 0;
                    }
                    foreach ($valores as $v) {
                        $txt = trim((string)$v);
                        if ($txt !== '') {
                            $conteoOpciones[$txt] = ($conteoOpciones[$txt] ?? 0) + 1;
                        }
                    }
                    $resumenPregunta['opciones'] = $opciones;
                    $resumenPregunta['conteo_opciones'] = $conteoOpciones;
                } else {
                    $resumenPregunta['respuestas'] = array_values(array_filter(
                        array_map('trim', $valores),
                        fn($txt) => $txt !== ''
                    ));
                }

                $preguntasFormateadas[] = $resumenPregunta;
            }

            $resultado[] = [
                'id_encuesta'        => $idEnc,
                'id_categoria'       => (int)$enc['id_categoria'],
                'categoria_nombre'   => $enc['categoria_nombre'],
                'comentarios'        => $enc['comentarios'],
                'estado'             => $enc['estado'],
                'total_resoluciones' => count($resoluciones),
                'preguntas'          => $preguntasFormateadas,
                'observaciones'      => $observaciones
            ];
        }

        return $resultado;
    }

    // ---------------------------------------------------------------- Gestión de Encuestas y Preguntas (CRUD)

    public function obtenerEncuestaPorId(int $id): ?array {
        $stmt = $this->conexion->prepare("SELECT * FROM encuesta WHERE id_encuesta = :id LIMIT 1");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    // Crea una encuesta para una categoría (si pasa a Activa, cierra las anteriores de esa misma categoría).
    public function crearEncuesta(int $id_categoria, string $comentarios, string $estado = 'Activa'): ?int {
        $this->ultimoError = '';
        $this->codigoError = 400;

        if ($id_categoria <= 0 || trim($comentarios) === '') {
            $this->ultimoError = 'La categoría y el título de la encuesta son obligatorios.';
            return null;
        }
        if (!in_array($estado, ['Borrador', 'Activa', 'Cerrada'], true)) {
            $this->ultimoError = 'El estado de la encuesta no es válido.';
            return null;
        }

        try {
            $this->conexion->beginTransaction();

            // Si la nueva encuesta queda Activa, se cierran otras encuestas activas de esa misma categoría
            if ($estado === 'Activa') {
                $stmtCerrar = $this->conexion->prepare(
                    "UPDATE encuesta SET estado = 'Cerrada' WHERE id_categoria = :id_categoria AND estado = 'Activa'"
                );
                $stmtCerrar->bindValue(':id_categoria', $id_categoria, PDO::PARAM_INT);
                $stmtCerrar->execute();
            }

            $stmt = $this->conexion->prepare(
                "INSERT INTO encuesta (comentarios, estado, id_categoria) VALUES (:comentarios, :estado, :id_categoria)"
            );
            $stmt->bindValue(':comentarios', trim($comentarios), PDO::PARAM_STR);
            $stmt->bindValue(':estado', $estado, PDO::PARAM_STR);
            $stmt->bindValue(':id_categoria', $id_categoria, PDO::PARAM_INT);
            $stmt->execute();
            $idNueva = (int)$this->conexion->lastInsertId();

            $this->conexion->commit();
            return $idNueva;
        } catch (Exception $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            $this->ultimoError = 'Error al crear la encuesta.';
            $this->codigoError = 500;
            return null;
        }
    }

    // Actualiza el comentario/título y el estado ('Borrador', 'Activa', 'Cerrada') de una encuesta.
    public function actualizarEncuesta(int $id_encuesta, string $comentarios, string $estado): bool {
        $this->ultimoError = '';
        $this->codigoError = 400;

        $actual = $this->obtenerEncuestaPorId($id_encuesta);
        if ($actual === null) {
            $this->ultimoError = 'Encuesta no encontrada.';
            $this->codigoError = 404;
            return false;
        }
        if (trim($comentarios) === '' || !in_array($estado, ['Borrador', 'Activa', 'Cerrada'], true)) {
            $this->ultimoError = 'El título y un estado válido (Borrador, Activa o Cerrada) son obligatorios.';
            return false;
        }

        try {
            $this->conexion->beginTransaction();

            // Si pasa a Activa, cerramos cualquier otra encuesta activa de esa misma categoría
            if ($estado === 'Activa') {
                $stmtCerrar = $this->conexion->prepare(
                    "UPDATE encuesta SET estado = 'Cerrada'
                     WHERE id_categoria = :id_categoria AND id_encuesta <> :id_encuesta AND estado = 'Activa'"
                );
                $stmtCerrar->bindValue(':id_categoria', (int)$actual['id_categoria'], PDO::PARAM_INT);
                $stmtCerrar->bindValue(':id_encuesta', $id_encuesta, PDO::PARAM_INT);
                $stmtCerrar->execute();
            }

            $stmt = $this->conexion->prepare(
                "UPDATE encuesta SET comentarios = :comentarios, estado = :estado WHERE id_encuesta = :id"
            );
            $stmt->bindValue(':comentarios', trim($comentarios), PDO::PARAM_STR);
            $stmt->bindValue(':estado', $estado, PDO::PARAM_STR);
            $stmt->bindValue(':id', $id_encuesta, PDO::PARAM_INT);
            $stmt->execute();

            $this->conexion->commit();
            return true;
        } catch (Exception $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            $this->ultimoError = 'Error al actualizar la encuesta.';
            $this->codigoError = 500;
            return false;
        }
    }

    // Elimina una encuesta completa (preguntas, opciones y resoluciones se eliminan en cascada según FK).
    public function eliminarEncuesta(int $id_encuesta): bool {
        $this->ultimoError = '';
        if ($this->obtenerEncuestaPorId($id_encuesta) === null) {
            $this->ultimoError = 'Encuesta no encontrada.';
            $this->codigoError = 404;
            return false;
        }
        $stmt = $this->conexion->prepare("DELETE FROM encuesta WHERE id_encuesta = :id");
        $stmt->bindValue(':id', $id_encuesta, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function obtenerPreguntaPorId(int $id_pregunta): ?array {
        $stmt = $this->conexion->prepare("SELECT * FROM pregunta WHERE id_pregunta = :id LIMIT 1");
        $stmt->bindValue(':id', $id_pregunta, PDO::PARAM_INT);
        $stmt->execute();
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    // Crea una pregunta dentro de una encuesta (y sus opciones si es Opcion_Multiple).
    public function crearPregunta(int $id_encuesta, string $enunciado, string $tipo, array $opciones = []): ?int {
        $this->ultimoError = '';
        $this->codigoError = 400;

        if ($this->obtenerEncuestaPorId($id_encuesta) === null) {
            $this->ultimoError = 'La encuesta indicada no existe.';
            $this->codigoError = 404;
            return null;
        }
        $enunciado = trim($enunciado);
        if ($enunciado === '' || !in_array($tipo, ['Si/No', 'Texto Libre', 'Escala_1_5', 'Opcion_Multiple'], true)) {
            $this->ultimoError = 'El enunciado y un tipo de pregunta válido son obligatorios.';
            return null;
        }
        $opcionesLimpias = array_values(array_filter(array_map('trim', $opciones), fn($x) => $x !== ''));
        if ($tipo === 'Opcion_Multiple' && count($opcionesLimpias) < 2) {
            $this->ultimoError = 'Una pregunta de Opción Múltiple requiere al menos 2 opciones.';
            return null;
        }

        try {
            $this->conexion->beginTransaction();

            $stmt = $this->conexion->prepare(
                "INSERT INTO pregunta (enunciado, tipo, id_encuesta) VALUES (:enunciado, :tipo, :id_encuesta)"
            );
            $stmt->bindValue(':enunciado', $enunciado, PDO::PARAM_STR);
            $stmt->bindValue(':tipo', $tipo, PDO::PARAM_STR);
            $stmt->bindValue(':id_encuesta', $id_encuesta, PDO::PARAM_INT);
            $stmt->execute();
            $id_pregunta = (int)$this->conexion->lastInsertId();

            if ($tipo === 'Opcion_Multiple') {
                $stmtOp = $this->conexion->prepare(
                    "INSERT INTO opcion_pregunta (texto_opcion, id_pregunta) VALUES (:texto, :id_pregunta)"
                );
                foreach ($opcionesLimpias as $texto) {
                    $stmtOp->bindValue(':texto', $texto, PDO::PARAM_STR);
                    $stmtOp->bindValue(':id_pregunta', $id_pregunta, PDO::PARAM_INT);
                    $stmtOp->execute();
                }
            }

            $this->conexion->commit();
            return $id_pregunta;
        } catch (Exception $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            $this->ultimoError = 'Error al registrar la pregunta.';
            $this->codigoError = 500;
            return null;
        }
    }

    // Actualiza el enunciado y el tipo de una pregunta existente.
    public function actualizarPregunta(int $id_pregunta, string $enunciado, string $tipo, array $opciones = []): bool {
        $this->ultimoError = '';
        $this->codigoError = 400;

        if ($this->obtenerPreguntaPorId($id_pregunta) === null) {
            $this->ultimoError = 'Pregunta no encontrada.';
            $this->codigoError = 404;
            return false;
        }
        $enunciado = trim($enunciado);
        if ($enunciado === '' || !in_array($tipo, ['Si/No', 'Texto Libre', 'Escala_1_5', 'Opcion_Multiple'], true)) {
            $this->ultimoError = 'El enunciado y un tipo de pregunta válido son obligatorios.';
            return false;
        }
        $opcionesLimpias = array_values(array_filter(array_map('trim', $opciones), fn($x) => $x !== ''));
        if ($tipo === 'Opcion_Multiple' && count($opcionesLimpias) < 2) {
            $this->ultimoError = 'Una pregunta de Opción Múltiple requiere al menos 2 opciones.';
            return false;
        }

        try {
            $this->conexion->beginTransaction();

            $stmt = $this->conexion->prepare(
                "UPDATE pregunta SET enunciado = :enunciado, tipo = :tipo WHERE id_pregunta = :id"
            );
            $stmt->bindValue(':enunciado', $enunciado, PDO::PARAM_STR);
            $stmt->bindValue(':tipo', $tipo, PDO::PARAM_STR);
            $stmt->bindValue(':id', $id_pregunta, PDO::PARAM_INT);
            $stmt->execute();

            $stmtDel = $this->conexion->prepare("DELETE FROM opcion_pregunta WHERE id_pregunta = :id");
            $stmtDel->bindValue(':id', $id_pregunta, PDO::PARAM_INT);
            $stmtDel->execute();

            if ($tipo === 'Opcion_Multiple') {
                $stmtOp = $this->conexion->prepare(
                    "INSERT INTO opcion_pregunta (texto_opcion, id_pregunta) VALUES (:texto, :id_pregunta)"
                );
                foreach ($opcionesLimpias as $texto) {
                    $stmtOp->bindValue(':texto', $texto, PDO::PARAM_STR);
                    $stmtOp->bindValue(':id_pregunta', $id_pregunta, PDO::PARAM_INT);
                    $stmtOp->execute();
                }
            }

            $this->conexion->commit();
            return true;
        } catch (Exception $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            $this->ultimoError = 'Error al actualizar la pregunta.';
            $this->codigoError = 500;
            return false;
        }
    }

    // Elimina una pregunta (sus respuestas y opciones se eliminan en cascada según FK ON DELETE CASCADE).
    public function eliminarPregunta(int $id_pregunta): bool {
        $this->ultimoError = '';
        if ($this->obtenerPreguntaPorId($id_pregunta) === null) {
            $this->ultimoError = 'Pregunta no encontrada.';
            $this->codigoError = 404;
            return false;
        }
        $stmt = $this->conexion->prepare("DELETE FROM pregunta WHERE id_pregunta = :id");
        $stmt->bindValue(':id', $id_pregunta, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
