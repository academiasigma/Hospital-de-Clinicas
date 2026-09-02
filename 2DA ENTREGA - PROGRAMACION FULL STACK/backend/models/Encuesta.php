<?php

require_once __DIR__ . '/../config/Database.php';

class Encuesta {
    private ?PDO $conexion;
    private string $tabla = 'encuesta';

    public function __construct() {
        $db = new Database();
        $this->conexion = $db->conectar();
    }

    //Obtiene la encuesta activa de una categoría específica, con sus preguntas y opciones. 
    public function obtenerActivaPorCategoria(int $id_categoria): ?array {
        $sql = "SELECT id_encuesta, comentarios, id_categoria 
                FROM {$this->tabla} 
                WHERE id_categoria = :id_categoria AND estado = 'Activa' 
                LIMIT 1";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id_categoria', $id_categoria, PDO::PARAM_INT);
        $stmt->execute();
        
        $encuesta = $stmt->fetch();
        if (!$encuesta) {
            return null;
        }

        $id_encuesta = (int)$encuesta['id_encuesta'];

        // Obtener preguntas de la encuesta
        $sqlPreguntas = "SELECT id_pregunta, enunciado, tipo 
 FROM pregunta 
 WHERE id_encuesta = :id_encuesta";
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
 WHERE id_pregunta = :id_pregunta";
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

    
     // Guarda de forma anónima y transaccional una resolución de encuesta con sus respectivas respuestas.
    public function guardarResolucion(int $id_encuesta, string $observaciones, array $respuestas): bool {
        try {
            $this->conexion->beginTransaction();

            //insertar resolución
            $sqlResolucion = "INSERT INTO resolucion_encuesta (id_encuesta, observaciones) 
 VALUES (:id_encuesta, :observaciones)";
            $stmtRes = $this->conexion->prepare($sqlResolucion);
            $stmtRes->bindParam(':id_encuesta', $id_encuesta, PDO::PARAM_INT);
            $stmtRes->bindParam(':observaciones', $observaciones, PDO::PARAM_STR);
            $stmtRes->execute();

            $id_resolucion = (int)$this->conexion->lastInsertId();

            //insertar cada respuesta
            $sqlRespuesta = "INSERT INTO respuesta (contenido_respuesta, id_resolucion, id_pregunta) 
 VALUES (:contenido, :id_resolucion, :id_pregunta)";
            $stmtResp = $this->conexion->prepare($sqlRespuesta);

            foreach ($respuestas as $resp) {
                $id_pregunta = (int)$resp['id_pregunta'];
                $contenido = trim($resp['contenido_respuesta'] ?? '');

                // Saltamos las respuestas vacías si el usuario no contestó
                if ($contenido === '') {
                    continue;
                }

                $stmtResp->bindParam(':contenido', $contenido, PDO::PARAM_STR);
                $stmtResp->bindParam(':id_resolucion', $id_resolucion, PDO::PARAM_INT);
                $stmtResp->bindParam(':id_pregunta', $id_pregunta, PDO::PARAM_INT);
                $stmtResp->execute();
            }

            $this->conexion->commit();
            return true;
        } catch (Exception $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            error_log("Error al guardar resolucion: " . $e->getMessage());
            return false;
        }
    }
}
