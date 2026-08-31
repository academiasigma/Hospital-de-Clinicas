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

    // Busca un usuario por su dirección de correo institucional.
     
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

    //Valida las credenciales recibidas contra el hash de la base de datos y el estado activo.
     
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

        // Verificación criptográfica con password_verify
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
}
