<?php
// api_usuarios.php - API para manejar operaciones CRUD de usuarios con MySQLi
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/conexion.php';

class UsuarioController {
    private $conn;
    
    public function __construct($conexion) {
        $this->conn = $conexion;
    }
    

 public function obtenerUsuarios() {
    try {
        $stmt = $this->conn->prepare("CALL ObtenerAdministradores()");
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result) {
            $usuarios = [];
            while ($row = $result->fetch_assoc()) {
                $usuarios[] = $row;
            }

            jsonResponse([
                'success' => true,
                'data' => $usuarios
            ]);
        } else {
            jsonResponse([
                'success' => false,
                'message' => 'Error al obtener usuarios.'
            ], 500);
        }
    } catch(Exception $e) {
        jsonResponse([
            'success' => false,
            'message' => 'Error al obtener usuarios: ' . $e->getMessage()
        ], 500);
    }
}

    
    // Crear nuevo usuario
    public function crearUsuario($data) {
        try {
            // Validar datos requeridos
            if (empty($data['nombre']) || empty($data['apellido']) || empty($data['usuario']) || empty($data['contrasena'])) {
                jsonResponse([
                    'success' => false,
                    'message' => 'Todos los campos son obligatorios'
                ], 400);
            }
            
            // Escapar datos para evitar inyección SQL
            $nombre = $this->conn->real_escape_string($data['nombre']);
            $apellido = $this->conn->real_escape_string($data['apellido']);
            $usuario = $this->conn->real_escape_string($data['usuario']);
            $contrasena = $this->conn->real_escape_string($data['contrasena']);
            
            // Verificar si el usuario ya existe
            $checkQuery = "SELECT COUNT(*) as count FROM administradores WHERE usuario = '$usuario'";
            $checkResult = $this->conn->query($checkQuery);
            
            if ($checkResult && $checkResult->fetch_assoc()['count'] > 0) {
                jsonResponse([
                    'success' => false,
                    'message' => 'El nombre de usuario ya existe'
                ], 400);
            }
            
            // Hash de la contraseña por seguridad
            $hashedPassword = password_hash($contrasena, PASSWORD_DEFAULT);
            
            // Insertar nuevo usuario
            $query = "INSERT INTO administradores (nombre, apellido, usuario, contrasena) VALUES ('$nombre', '$apellido', '$usuario', '$hashedPassword')";
            
            if ($this->conn->query($query)) {
                jsonResponse([
                    'success' => true,
                    'message' => 'Usuario creado exitosamente',
                    'id' => $this->conn->insert_id
                ]);
            } else {
                jsonResponse([
                    'success' => false,
                    'message' => 'Error al crear usuario: ' . $this->conn->error
                ], 500);
            }
        } catch(Exception $e) {
            jsonResponse([
                'success' => false,
                'message' => 'Error al crear usuario: ' . $e->getMessage()
            ], 500);
        }
    }
    
    // Actualizar usuario
    public function actualizarUsuario($id, $data) {
        try {
            // Validar datos requeridos
            if (empty($data['nombre']) || empty($data['apellido']) || empty($data['usuario'])) {
                jsonResponse([
                    'success' => false,
                    'message' => 'Nombre, apellido y usuario son obligatorios'
                ], 400);
            }
            
            // Escapar datos
            $id = (int)$id;
            $nombre = $this->conn->real_escape_string($data['nombre']);
            $apellido = $this->conn->real_escape_string($data['apellido']);
            $usuario = $this->conn->real_escape_string($data['usuario']);
            
            // Verificar si el usuario existe
            $checkQuery = "SELECT COUNT(*) as count FROM administradores WHERE id_admin = $id";
            $checkResult = $this->conn->query($checkQuery);
            
            if (!$checkResult || $checkResult->fetch_assoc()['count'] == 0) {
                jsonResponse([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }
            
            // Verificar si el nuevo nombre de usuario ya existe (excepto el actual)
            $checkUserQuery = "SELECT COUNT(*) as count FROM administradores WHERE usuario = '$usuario' AND id_admin != $id";
            $checkUserResult = $this->conn->query($checkUserQuery);
            
            if ($checkUserResult && $checkUserResult->fetch_assoc()['count'] > 0) {
                jsonResponse([
                    'success' => false,
                    'message' => 'El nombre de usuario ya existe'
                ], 400);
            }
            
            // Preparar query de actualización
            if (!empty($data['contrasena'])) {
                $contrasena = $this->conn->real_escape_string($data['contrasena']);
                $hashedPassword = password_hash($contrasena, PASSWORD_DEFAULT);
                $query = "UPDATE administradores SET nombre = '$nombre', apellido = '$apellido', usuario = '$usuario', contrasena = '$hashedPassword' WHERE id_admin = $id";
            } else {
                $query = "UPDATE administradores SET nombre = '$nombre', apellido = '$apellido', usuario = '$usuario' WHERE id_admin = $id";
            }
            
            if ($this->conn->query($query)) {
                jsonResponse([
                    'success' => true,
                    'message' => 'Usuario actualizado exitosamente'
                ]);
            } else {
                jsonResponse([
                    'success' => false,
                    'message' => 'Error al actualizar usuario: ' . $this->conn->error
                ], 500);
            }
        } catch(Exception $e) {
            jsonResponse([
                'success' => false,
                'message' => 'Error al actualizar usuario: ' . $e->getMessage()
            ], 500);
        }
    }
    


    public function eliminarUsuario($id) {
        try {
            $id = (int)$id;
    
            $stmt = $this->conn->prepare("CALL EliminarAdmin(?)");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                jsonResponse([
                    'success' => true,
                    'message' => 'Usuario eliminado exitosamente'
                ]);
            } else {
                jsonResponse([
                    'success' => false,
                    'message' => 'Error al ejecutar el procedimiento: ' . $this->conn->error
                ], 500);
            }
        } catch(Exception $e) {
            jsonResponse([
                'success' => false,
                'message' => 'Error al eliminar usuario: ' . $e->getMessage()
            ], 500);
        }
    }
    
}

// Manejar las peticiones
$method = $_SERVER['REQUEST_METHOD'];
$controller = new UsuarioController($conn);

switch($method) {
    case 'GET':
        $controller->obtenerUsuarios();
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if ($data === null) {
            jsonResponse(['success' => false, 'message' => 'Datos JSON inválidos'], 400);
        }
        $controller->crearUsuario($data);
        break;
        
    case 'PUT':
        $id = $_GET['id'] ?? null;
        if (!$id) {
            jsonResponse(['success' => false, 'message' => 'ID requerido'], 400);
        }
        $data = json_decode(file_get_contents('php://input'), true);
        if ($data === null) {
            jsonResponse(['success' => false, 'message' => 'Datos JSON inválidos'], 400);
        }
        $controller->actualizarUsuario($id, $data);
        break;
        
    case 'DELETE':
        $id = $_GET['id'] ?? null;
        if (!$id) {
            jsonResponse(['success' => false, 'message' => 'ID requerido'], 400);
        }
        $controller->eliminarUsuario($id);
        break;
        
    default:
        jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        break;
}

// Cerrar conexión
$conn->close();
?>