<?php
// entrenadorAPI.php - API para manejar operaciones CRUD de entrenadores
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/conexion.php';

// Obtener el método HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Obtener datos JSON para POST, PUT, PATCH
$input = json_decode(file_get_contents('php://input'), true);

// Log para debugging (opcional - remover en producción)
error_log("Método: " . $method);
error_log("Datos recibidos: " . json_encode($input));

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['filtro']) && $_GET['filtro'] === 'especialidad') {
                obtenerEntrenadorPorEspecialidad($conn);
            } else {
                obtenerEntrenadores($conn);
            }
            break;
        
        case 'POST':
            crearEntrenador($conn, $input);
            break;
        
        case 'PUT':
            actualizarEntrenador($conn, $input);
            break;
        
        case 'DELETE':
            eliminarEntrenador($conn);
            break;
        
        case 'PATCH':
            asignarCategoria($conn, $input);
            break;
        
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}

// Función para obtener todos los entrenadores con sus categorías asignadas
function obtenerEntrenadores($conn) {
    try {
        // Llamar al procedimiento almacenado
        $result = $conn->query("CALL obtenerEntrenadores()");
        
        $entrenadores = [];
        if ($result && $result->num_rows > 0) {
            while ($fila = $result->fetch_assoc()) {
                $entrenadores[] = $fila;
            }
        }
        
        echo json_encode($entrenadores);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al obtener entrenadores: ' . $e->getMessage()]);
    }
}


function obtenerEntrenadorPorEspecialidad($conn) {
    try {
        $especialidad = $_GET['especialidad'] ?? '';
        
        if (empty($especialidad)) {
            // Si no hay especialidad, llamar a la función original
            obtenerEntrenadores($conn);
            return;
        }
        
        // Preparar la llamada al procedimiento almacenado
        $stmt = $conn->prepare("CALL obtenerEntrenadorPorEspecialidad(?)");
        $stmt->bind_param("s", $especialidad);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        $entrenadores = [];
        if ($result && $result->num_rows > 0) {
            while ($fila = $result->fetch_assoc()) {
                $entrenadores[] = $fila;
            }
        }
        
        $stmt->close();
        echo json_encode($entrenadores);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al filtrar entrenadores: ' . $e->getMessage()]);
    }
}





// Función para crear un nuevo entrenador
function crearEntrenador($conn, $data) {
    try {
        if (!$data) {
            echo json_encode(['success' => false, 'message' => 'No se recibieron datos']);
            return;
        }
        
        // Validar datos requeridos
        if (empty($data['cedula']) || empty($data['nombre']) || empty($data['apellido'])) {
            echo json_encode(['success' => false, 'message' => 'Cédula, nombre y apellido son requeridos']);
            return;
        }
        
        // Preparar parámetros
        $cedula = $data['cedula'];
        $nombre = $data['nombre'];
        $apellido = $data['apellido'];
        $telefono = isset($data['telefono']) ? $data['telefono'] : null;
        $correo = isset($data['correo']) ? $data['correo'] : null;
        $especialidad = isset($data['especialidad']) ? $data['especialidad'] : null;
        $experiencia = isset($data['experiencia_años']) ? intval($data['experiencia_años']) : null;
        $fecha_contratacion = isset($data['fecha_contratacion']) ? $data['fecha_contratacion'] : null;
        
        // Llamar al procedimiento almacenado
        $stmt = $conn->prepare("CALL registrarEntrenadores(?, ?, ?, ?, ?, ?, ?, ?, @resultado, @mensaje, @id_insertado)");
        $stmt->bind_param("ssssssss", $cedula, $nombre, $apellido, $telefono, $correo, $especialidad, $experiencia, $fecha_contratacion);
        $stmt->execute();
        $stmt->close();
        
        // Obtener los resultados
        $result = $conn->query("SELECT @resultado as resultado, @mensaje as mensaje, @id_insertado as id_insertado");
        $row = $result->fetch_assoc();
        
        if ($row['resultado'] == 1) {
            echo json_encode([
                'success' => true, 
                'message' => $row['mensaje'], 
                'id' => $row['id_insertado']
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => $row['mensaje']
            ]);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al crear entrenador: ' . $e->getMessage()]);
    }
}




 // Actualizar Entrenador
function actualizarEntrenador($conn, $data) {
    try {
        if (!$data || empty($data['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID del entrenador es requerido']);
            return;
        }
        
        // Preparar parámetros
        $id = $data['id'];
        $nombre = $data['nombre'] ?? '';
        $apellido = $data['apellido'] ?? '';
        $telefono = isset($data['telefono']) ? $data['telefono'] : null;
        $correo = isset($data['correo']) ? $data['correo'] : null;
        $especialidad = isset($data['especialidad']) ? $data['especialidad'] : null;
        $experiencia = isset($data['experiencia_años']) ? intval($data['experiencia_años']) : null;
        $fecha_contratacion = isset($data['fecha_contratacion']) ? $data['fecha_contratacion'] : null;
        
        // Llamar al procedimiento almacenado
        $stmt = $conn->prepare("CALL actualizarEntrenador(?, ?, ?, ?, ?, ?, ?, ?, @resultado, @mensaje)");
        $stmt->bind_param("ssssssss", $id, $nombre, $apellido, $telefono, $correo, $especialidad, $experiencia, $fecha_contratacion);
        $stmt->execute();
        $stmt->close();
        
        // Obtener los resultados
        $result = $conn->query("SELECT @resultado as resultado, @mensaje as mensaje");
        $row = $result->fetch_assoc();
        
        echo json_encode([
            'success' => ($row['resultado'] == 1), 
            'message' => $row['mensaje']
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar entrenador: ' . $e->getMessage()]);
    }
}

// Función para eliminar un entrenador
function eliminarEntrenador($conn) {
    try {
        $id = $_GET['id'] ?? null;
        
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'ID del entrenador es requerido']);
            return;
        }
        
        // Llamar al procedimiento almacenado
        $stmt = $conn->prepare("CALL eliminarEntrenador(?, @resultado, @mensaje)");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $stmt->close();
        
        // Obtener los resultados
        $result = $conn->query("SELECT @resultado as resultado, @mensaje as mensaje");
        $row = $result->fetch_assoc();
        
        echo json_encode([
            'success' => ($row['resultado'] == 1), 
            'message' => $row['mensaje']
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar entrenador: ' . $e->getMessage()]);
    }
}

// Función para asignar categoría a un entrenador
function asignarCategoria($conn, $data) {
    try {
        if (!$data || empty($data['id']) || empty($data['categoria'])) {
            echo json_encode(['success' => false, 'message' => 'ID del entrenador y categoría son requeridos']);
            return;
        }
        
        // Llamar al procedimiento almacenado
        $stmt = $conn->prepare("CALL asignarCategoria(?, ?, @resultado, @mensaje)");
        $stmt->bind_param("ss", $data['id'], $data['categoria']);
        $stmt->execute();
        $stmt->close();
        
        // Obtener los resultados
        $result = $conn->query("SELECT @resultado as resultado, @mensaje as mensaje");
        $row = $result->fetch_assoc();
        
        echo json_encode([
            'success' => ($row['resultado'] == 1), 
            'message' => $row['mensaje']
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al asignar categoría: ' . $e->getMessage()]);
    }
}

// Cerrar la conexión
if (isset($conn)) {
    $conn->close();
}
?>