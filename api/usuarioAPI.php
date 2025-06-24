<?php
session_start(); // Iniciar sesión al principio del script

// --- Función para enviar respuestas JSON ---
// Esta función debe estar definida LO MÁS ARRIBA POSIBLE,
// antes de cualquier posible salida o error de inclusión.
function jsonResponse($data, $statusCode = 200) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit(); // ¡Importante! Detener la ejecución después de enviar la respuesta JSON.
}
// --- Fin Función para enviar respuestas JSON ---

// Configuración de encabezados CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization'); // Añadir Authorization si lo usas para tokens

// Manejar preflight requests (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Lógica de autenticación (similar a partidosAPI.php)
$public_get_allowed_apis = []; // Ajusta esto si alguna operación GET de usuario es pública
$current_script_name = basename($_SERVER['PHP_SELF']);

// Si la sesión no está iniciada O el método no es GET y no es una API permitida públicamente
if (
    !isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true
    && !(
        $_SERVER['REQUEST_METHOD'] === 'GET' &&
        in_array($current_script_name, $public_get_allowed_apis)
    )
) {
    jsonResponse(['success' => false, 'message' => 'Acceso no autorizado. Inicia sesión para realizar esta acción.'], 401);
}


// INICIO DEL BLOQUE TRY PRINCIPAL PARA MANEJAR ERRORES DE CONEXIÓN O EJECUCIÓN
try {
    // Incluir la conexión DENTRO del try-catch principal.
    // Si conexion.php lanza una Exception, será capturada aquí.
    require_once '../config/conexion.php';

    // Definición de la clase UsuarioController
    class UsuarioController {
        private $conn;

        public function __construct($conexion) {
            $this->conn = $conexion;
        }

        public function obtenerUsuarios() {
            try {
                // Si 'administradores' es una tabla y no un SP, cambia la consulta.
                // Si 'ObtenerAdministradores' es un SP, asegúrate de que exista y funcione.
                $stmt = $this->conn->prepare("CALL ObtenerAdministradores()");
                
                // Si la preparación falla, lanza excepción.
                if (!$stmt) {
                    throw new Exception("Fallo en la preparación de la consulta: " . $this->conn->error);
                }

                $stmt->execute();
                
                // Si la ejecución falla, lanza excepción.
                if ($stmt->error) {
                    throw new Exception("Fallo en la ejecución de la consulta: " . $stmt->error);
                }

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
                    // Esto es menos probable si get_result() no dio error, pero por seguridad
                    jsonResponse([
                        'success' => false,
                        'message' => 'No se obtuvieron resultados válidos de la base de datos.'
                    ], 500);
                }
            } catch(Exception $e) {
                // Captura interna para mensajes específicos de la operación
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

                // Usar prepared statements para todas las consultas INSERT/UPDATE/DELETE.
                // Real_escape_string no es suficiente para consultas dinámicas y es menos seguro que prepared statements.
                $nombre = $data['nombre'];
                $apellido = $data['apellido'];
                $usuario = $data['usuario'];
                $contrasena = $data['contrasena']; // Ya que se va a hashear, no es un problema de SQLi directo aquí

                // Verificar si el usuario ya existe (usando prepared statement)
                $checkStmt = $this->conn->prepare("SELECT COUNT(*) as count FROM administradores WHERE usuario = ?");
                if (!$checkStmt) {
                    throw new Exception("Error en la preparación de la verificación de usuario: " . $this->conn->error);
                }
                $checkStmt->bind_param("s", $usuario);
                $checkStmt->execute();
                $checkResult = $checkStmt->get_result()->fetch_assoc();
                $checkStmt->close();

                if ($checkResult['count'] > 0) {
                    jsonResponse([
                        'success' => false,
                        'message' => 'El nombre de usuario ya existe'
                    ], 400);
                }

                // Hash de la contraseña por seguridad
                $hashedPassword = password_hash($contrasena, PASSWORD_DEFAULT);

                // Insertar nuevo usuario (usando prepared statement)
                $insertStmt = $this->conn->prepare("INSERT INTO administradores (nombre, apellido, usuario, contrasena) VALUES (?, ?, ?, ?)");
                if (!$insertStmt) {
                    throw new Exception("Error en la preparación de la inserción de usuario: " . $this->conn->error);
                }
                $insertStmt->bind_param("ssss", $nombre, $apellido, $usuario, $hashedPassword);

                if ($insertStmt->execute()) {
                    jsonResponse([
                        'success' => true,
                        'message' => 'Usuario creado exitosamente',
                        'id' => $this->conn->insert_id
                    ]);
                } else {
                    jsonResponse([
                        'success' => false,
                        'message' => 'Error al crear usuario: ' . $insertStmt->error
                    ], 500);
                }
                $insertStmt->close();
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

                $id = (int)$id;
                $nombre = $data['nombre'];
                $apellido = $data['apellido'];
                $usuario = $data['usuario'];

                // Verificar si el usuario existe (usando prepared statement)
                $checkIdStmt = $this->conn->prepare("SELECT COUNT(*) as count FROM administradores WHERE id_admin = ?");
                if (!$checkIdStmt) {
                    throw new Exception("Error en la preparación de la verificación de ID de usuario: " . $this->conn->error);
                }
                $checkIdStmt->bind_param("i", $id);
                $checkIdStmt->execute();
                $checkIdResult = $checkIdStmt->get_result()->fetch_assoc();
                $checkIdStmt->close();

                if ($checkIdResult['count'] == 0) {
                    jsonResponse([
                        'success' => false,
                        'message' => 'Usuario no encontrado'
                    ], 404);
                }

                // Verificar si el nuevo nombre de usuario ya existe (excepto el actual)
                $checkUserExistsStmt = $this->conn->prepare("SELECT COUNT(*) as count FROM administradores WHERE usuario = ? AND id_admin != ?");
                if (!$checkUserExistsStmt) {
                    throw new Exception("Error en la preparación de la verificación de usuario existente: " . $this->conn->error);
                }
                $checkUserExistsStmt->bind_param("si", $usuario, $id);
                $checkUserExistsStmt->execute();
                $checkUserExistsResult = $checkUserExistsStmt->get_result()->fetch_assoc();
                $checkUserExistsStmt->close();

                if ($checkUserExistsResult['count'] > 0) {
                    jsonResponse([
                        'success' => false,
                        'message' => 'El nombre de usuario ya existe'
                    ], 400);
                }

                // Preparar query de actualización (usando prepared statement)
                $query = "";
                if (!empty($data['contrasena'])) {
                    $contrasena = $data['contrasena'];
                    $hashedPassword = password_hash($contrasena, PASSWORD_DEFAULT);
                    $query = "UPDATE administradores SET nombre = ?, apellido = ?, usuario = ?, contrasena = ? WHERE id_admin = ?";
                    $updateStmt = $this->conn->prepare($query);
                    if (!$updateStmt) {
                        throw new Exception("Error en la preparación de la actualización con contraseña: " . $this->conn->error);
                    }
                    $updateStmt->bind_param("ssssi", $nombre, $apellido, $usuario, $hashedPassword, $id);
                } else {
                    $query = "UPDATE administradores SET nombre = ?, apellido = ?, usuario = ? WHERE id_admin = ?";
                    $updateStmt = $this->conn->prepare($query);
                    if (!$updateStmt) {
                        throw new Exception("Error en la preparación de la actualización sin contraseña: " . $this->conn->error);
                    }
                    $updateStmt->bind_param("sssi", $nombre, $apellido, $usuario, $id);
                }

                if ($updateStmt->execute()) {
                    if ($updateStmt->affected_rows > 0) {
                        jsonResponse([
                            'success' => true,
                            'message' => 'Usuario actualizado exitosamente'
                        ]);
                    } else {
                        jsonResponse([
                            'success' => false,
                            'message' => 'Usuario no encontrado o sin cambios para actualizar'
                        ], 404);
                    }
                } else {
                    jsonResponse([
                        'success' => false,
                        'message' => 'Error al actualizar usuario: ' . $updateStmt->error
                    ], 500);
                }
                $updateStmt->close();
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
                if (!$stmt) {
                    throw new Exception("Fallo en la preparación para eliminar usuario: " . $this->conn->error);
                }
                $stmt->bind_param("i", $id);

                if ($stmt->execute()) {
                    // Si el SP no devuelve affected_rows directamente, puedes verificar si la operación fue exitosa.
                    // Para MySQLi, affected_rows funciona con SPs que modifican datos.
                    if ($stmt->affected_rows > 0) {
                        jsonResponse([
                            'success' => true,
                            'message' => 'Usuario eliminado exitosamente'
                        ]);
                    } else {
                        jsonResponse([
                            'success' => false,
                            'message' => 'Usuario no encontrado para eliminar o no se realizaron cambios.'
                        ], 404);
                    }
                } else {
                    jsonResponse([
                        'success' => false,
                        'message' => 'Error al ejecutar el procedimiento de eliminación: ' . $stmt->error
                    ], 500);
                }
                $stmt->close();
            } catch(Exception $e) {
                jsonResponse([
                    'success' => false,
                    'message' => 'Error al eliminar usuario: ' . $e->getMessage()
                ], 500);
            }
        }

    } // Fin de la clase UsuarioController


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
            $id = $_GET['id'] ?? null; // Obtener ID de la URL
            if (!$id) {
                jsonResponse(['success' => false, 'message' => 'ID requerido para actualizar'], 400);
            }
            $data = json_decode(file_get_contents('php://input'), true);
            if ($data === null) {
                jsonResponse(['success' => false, 'message' => 'Datos JSON inválidos'], 400);
            }
            $controller->actualizarUsuario($id, $data);
            break;

        case 'DELETE':
            $id = $_GET['id'] ?? null; // Obtener ID de la URL
            if (!$id) {
                jsonResponse(['success' => false, 'message' => 'ID requerido para eliminar'], 400);
            }
            $controller->eliminarUsuario($id);
            break;

        default:
            jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
            break;
    }

} catch (Exception $e) {
    // ESTE CATCH CAPTURA ERRORES CRÍTICOS, COMO EL FALLO DE CONEXIÓN DE conexion.php
    jsonResponse(['success' => false, 'message' => 'Error fatal en la API de usuarios: ' . $e->getMessage()], 500);
} finally {
    // Asegurarse de que la conexión se cierre si existe y no ha sido cerrada por un jsonResponse() anterior.
    // Aunque jsonResponse hace exit(), este `finally` es útil si el error es muy temprano.
    if (isset($conn) && is_object($conn)) {
        $conn->close();
    }
}
?>