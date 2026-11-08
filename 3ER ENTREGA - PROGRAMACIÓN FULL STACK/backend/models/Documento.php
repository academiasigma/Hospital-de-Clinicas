<?php

require_once __DIR__ . '/../config/Database.php';

class Documento {
    private ?PDO $conexion;
    private string $tabla = 'documento';

    public ?int $id_documento = null;
    public ?string $titulo = null;
    public ?string $archivo = null;
    
    public ?int $id_usuario = null;
    public ?int $id_categoria = null;

    public function __construct() {
        $db = new Database();
        $this->conexion = $db->conectar();
    }

     //Obtiene todos los documentos vinculando nombre de categoría y autor
    public function listarTodos(?int $idCategoria = null): array {
        $sql = "SELECT 
                    d.id_documento,
                    d.titulo,
                    d.archivo,
                    
                    d.id_categoria,
                    c.nombre AS categoria_nombre,
                    u.nombre AS autor_nombre
                FROM {$this->tabla} d
                INNER JOIN categoria c ON d.id_categoria = c.id_categoria
                INNER JOIN usuario u ON d.id_usuario = u.id_usuario";

        if ($idCategoria !== null && $idCategoria > 0) {
            $sql .= " WHERE d.id_categoria = :id_categoria";
        }

        $sql .= " ORDER BY d.id_documento DESC";

        $stmt = $this->conexion->prepare($sql);
        if ($idCategoria !== null && $idCategoria > 0) {
            $stmt->bindParam(':id_categoria', $idCategoria, PDO::PARAM_INT);
        }
        $stmt->execute();

        return $stmt->fetchAll();
    }

    
    // Obtiene todos los documentos para  la visualización en el portal de pacientes
    public function listarPublicos(): array {
        $sql = "SELECT 
                    d.id_documento,
                    d.titulo,
                    d.archivo,
                    
                    d.id_categoria,
                    c.nombre AS categoria_nombre
                FROM {$this->tabla} d
                INNER JOIN categoria c ON d.id_categoria = c.id_categoria
                ORDER BY d.titulo ASC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    
     // Obtiene un documento específico por su id único
    public function obtenerPorId(int $id): ?array {
        $sql = "SELECT 
                    d.id_documento,
                    d.titulo,
                    d.archivo,
                    
                    d.id_categoria,
                    c.nombre AS categoria_nombre,
                    d.id_usuario
                FROM {$this->tabla} d
                INNER JOIN categoria c ON d.id_categoria = c.id_categoria
                WHERE d.id_documento = :id
                LIMIT 1";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $resultado = $stmt->fetch();
        return $resultado ?: null;
    }

    
    //Registra un nuevo documento clínico en la base de datos
    public function crear(array $datos): bool {
        $sql = "INSERT INTO {$this->tabla} 
 (titulo, archivo, id_usuario, id_categoria) 
 VALUES 
 (:titulo, :archivo, :id_usuario, :id_categoria)";

        $stmt = $this->conexion->prepare($sql);

        $stmt->bindParam(':titulo', $datos['titulo'], PDO::PARAM_STR);
        $stmt->bindParam(':archivo', $datos['archivo'], PDO::PARAM_STR);
        
        $stmt->bindParam(':id_usuario', $datos['id_usuario'], PDO::PARAM_INT);
        $stmt->bindParam(':id_categoria', $datos['id_categoria'], PDO::PARAM_INT);

        return $stmt->execute();
    }

    
     //Actualiza los metadatos y opcionalmente el archivo de un documento existente
    public function actualizar(int $id, array $datos): bool {
        $docActual = $this->obtenerPorId($id);
        if (!$docActual) {
            return false;
        }

        $actualizarArchivo = !empty($datos['archivo']);
        $sql = "UPDATE {$this->tabla} 
 SET titulo = :titulo, 
     id_categoria = :id_categoria";

        if ($actualizarArchivo) {
            $sql .= ", archivo = :archivo";
        }

        $sql .= " WHERE id_documento = :id";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':titulo', $datos['titulo'], PDO::PARAM_STR);
        $stmt->bindParam(':id_categoria', $datos['id_categoria'], PDO::PARAM_INT);
        
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($actualizarArchivo) {
            $stmt->bindParam(':archivo', $datos['archivo'], PDO::PARAM_STR);
        }

        $ejecutado = $stmt->execute();

        // Limpieza física del archivo anterior si el update fue exitoso
        if ($ejecutado && $actualizarArchivo && !empty($docActual['archivo'])) {
            $rutaAnterior = __DIR__ . '/../../' . ltrim($docActual['archivo'], '/');
            if (file_exists($rutaAnterior)) {
                unlink($rutaAnterior);
            }
        }

        return $ejecutado;
    }

    // Elimina el registro del documento
    public function eliminar(int $id): bool {
        $doc = $this->obtenerPorId($id);
        if (!$doc) {
            return false;
        }

        $sql = "DELETE FROM {$this->tabla} WHERE id_documento = :id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        $eliminado = $stmt->execute();

        // Si se eliminó de la BD, borrar el archivo físico del servidor
        if ($eliminado && !empty($doc['archivo'])) {
            $rutaFisica = __DIR__ . '/../../' . ltrim($doc['archivo'], '/');
            if (file_exists($rutaFisica)) {
                unlink($rutaFisica);
            }
        }

        return $eliminado;
    }
}
