<?php
require_once __DIR__ . '/../config/Database.php';

class Usuario {
    private ?PDO $conexion;
    private string $tabla = 'usuario';

    public ?int $id_usuario = null;
    public ?string $email = null;
    public ?string $nombre = null;
    public ?string $contrasenia = null;
    public ?string $rol = null;
    public ?string $estado = null;

    public function __construct() {
        $db = new Database();
        $this->conexion = $db->conectar();
    }

     //Busca un usuario por su dirección de correo electrónico institucional
    public function obtenerPorEmail(string $email): ?array {
        $sql = "SELECT id_usuario, email, nombre, contrasenia, rol, estado 
 FROM {$this->tabla} 
 WHERE email = :email 
 LIMIT 1";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        $usuario = $stmt->fetch();
        return $usuario ?: null;
    }

    
     //Busca un usuario por su ID de clave primaria
    public function obtenerPorId(int $id): ?array {
        $sql = "SELECT id_usuario, email, nombre, contrasenia, rol, estado 
 FROM {$this->tabla} 
 WHERE id_usuario = :id 
 LIMIT 1";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $usuario = $stmt->fetch();
        return $usuario ?: null;
    }

    
     //Valida las credenciales recibidas contra el hash de la base de datos y el estado activo
    public function autenticar(string $email, string $password): array {
        $usuario = $this->obtenerPorEmail($email);

        if (!$usuario) {
            return [
                'exito'   => false,
                'codigo'  => 401,
                'mensaje' => 'Credenciales inválidas.'
            ];
        }

        // Validación de usuario activo
        if ($usuario['estado'] !== 'Activo') {
            return [
                'exito'   => false,
                'codigo'  => 403,
                'mensaje' => 'Cuenta deshabilitada. Comuníquese con el Administrador IT.'
            ];
        }

        // Verificación criptografica con password_verify
        if (!password_verify($password, $usuario['contrasenia'])) {
            return [
                'exito'   => false,
                'codigo'  => 401,
                'mensaje' => 'Credenciales inválidas.'
            ];
        }

        // Asignación interna
        $this->id_usuario = $usuario['id_usuario'];
        $this->email      = $usuario['email'];
        $this->nombre     = $usuario['nombre'];
        $this->rol        = $usuario['rol'];
        $this->estado     = $usuario['estado'];

        return [
            'exito'   => true,
            'codigo'  => 200,
            'mensaje' => 'Autenticación exitosa.',
            'usuario' => [
                'id_usuario' => $usuario['id_usuario'],
                'email'      => $usuario['email'],
                'nombre'     => $usuario['nombre'],
                'rol'        => $usuario['rol']
            ]
        ];
    }

     //Obtiene el listado completo de todos los usuarios de la institución
    public function listarTodos(): array {
        $sql = "SELECT id_usuario, email, nombre, rol, estado FROM {$this->tabla} ORDER BY nombre ASC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    
     // Registra de manera segura un nuevo funcionario en la institución
    public function crear(array $datos): bool {
        // Encriptación criptográfica (Secure Hash)
        $hashPassword = password_hash($datos['contrasenia'], PASSWORD_BCRYPT);

        $sql = "INSERT INTO {$this->tabla} (email, nombre, contrasenia, rol, estado) 
                VALUES (:email, :nombre, :contrasenia, :rol, :estado)";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':email', $datos['email'], PDO::PARAM_STR);
        $stmt->bindParam(':nombre', $datos['nombre'], PDO::PARAM_STR);
        $stmt->bindParam(':contrasenia', $hashPassword, PDO::PARAM_STR);
        $stmt->bindParam(':rol', $datos['rol'], PDO::PARAM_STR);
        $stmt->bindParam(':estado', $datos['estado'], PDO::PARAM_STR);

        return $stmt->execute();
    }

     //Actualiza de manera segura la información de un funcionario administrativo existente
    public function actualizar(int $id, array $datos): bool {
        $actualizarContrasenia = !empty($datos['contrasenia']);

        $sql = "UPDATE {$this->tabla} 
                SET email = :email, 
                    nombre = :nombre, 
                    rol = :rol, 
                    estado = :estado";

        if ($actualizarContrasenia) {
            $sql .= ", contrasenia = :contrasenia";
        }

        $sql .= " WHERE id_usuario = :id";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':email', $datos['email'], PDO::PARAM_STR);
        $stmt->bindParam(':nombre', $datos['nombre'], PDO::PARAM_STR);
        $stmt->bindParam(':rol', $datos['rol'], PDO::PARAM_STR);
        $stmt->bindParam(':estado', $datos['estado'], PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($actualizarContrasenia) {
            $hashPassword = password_hash($datos['contrasenia'], PASSWORD_BCRYPT);
            $stmt->bindParam(':contrasenia', $hashPassword, PDO::PARAM_STR);
        }

        return $stmt->execute();
    }

     //Elimina la ficha de un usuario administrativo por su ID de clave primaria.
    public function eliminar(int $id): bool {
        $sql = "DELETE FROM {$this->tabla} WHERE id_usuario = :id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
