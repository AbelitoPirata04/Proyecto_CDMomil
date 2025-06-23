<?php
// jugadorAPI.php - API para manejar jugadores
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/conexion.php';

try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            // CAMBIO CLAVE AQUÍ: Priorizar id_categoria si está presente
            if (isset($_GET['id_categoria'])) { // Si el frontend de partidos pide por categoría
                obtenerJugadoresPorCategoriaAPI($conn, $_GET['id_categoria']);
            } elseif (isset($_GET['filtrar'])) { // Si es una búsqueda filtrada general de jugadores
                busquedafiltradaJugadores($conn);
            } else { // Si no hay filtros, obtener todos los jugadores
                obtenerJugadores($conn);
            }
            break;
        case 'POST':
            // No necesitas pasar $conn explícitamente en el llamado si la función ya lo recibe globalmente o como parámetro en la definición.
            // Asegúrate que registarJugador reciba $conn
            registarJugador($conn); 
            break;
        case 'PUT':
            // Asegúrate que actualizarJugador reciba $conn
            actualizarJugador($conn);
            break;
        case 'DELETE':
            // Asegúrate que eliminarJugador reciba $conn
            eliminarJugador($conn);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}

// ===============================================================
// FUNCIÓN NUEVA / MODIFICADA: obtenerJugadoresPorCategoriaAPI
// Esta es la que llamará el modal de partidos
// ===============================================================
function obtenerJugadoresPorCategoriaAPI($conn, $id_categoria) {
    try {
        // Asegúrate de que 'obtenerJugadoresPorCategoria' es el nombre correcto de tu SP en la BD
        // que filtra por id_categoria
        $stmt = $conn->prepare("CALL obtenerJugadoresPorCategoria(?)"); 
        $stmt->bind_param("i", $id_categoria);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $jugadores = [];
        while ($fila = $resultado->fetch_assoc()) {
            // No olvides las conversiones a tipo numérico si tu frontend las necesita,
            // aunque en este contexto de un modal de estadísticas, probablemente no.
            $jugadores[] = $fila;
        }
        echo json_encode($jugadores, JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al obtener jugadores por categoría: ' . $e->getMessage()]);
    } finally {
        if (isset($stmt)) {
            $stmt->close(); // Cerrar el statement
        }
    }
}


// ===============================================================
// TUS FUNCIONES EXISTENTES (asegurando que cierren statement)
// ===============================================================

function busquedafiltradaJugadores($conn) {
    try {
        $busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : null;
        $posicion = isset($_GET['posicion']) ? $_GET['posicion'] : null;
        $genero = isset($_GET['genero']) ? $_GET['genero'] : null;
        $categoria = isset($_GET['categoria']) ? $_GET['categoria'] : null;
        
        // Asegúrate de que 'filtrarJugadores' es el nombre correcto de tu SP
        $stmt = $conn->prepare("CALL busquedafiltradaJugadores(?, ?, ?, ?)");
        $stmt->bind_param('ssss', $busqueda, $posicion, $genero, $categoria);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $jugadores = [];
        while ($fila = $resultado->fetch_assoc()) {
            // Mantener tus conversiones de tipo si las necesitas
            if ($fila['altura']) { $fila['altura'] = floatval($fila['altura']); }
            if ($fila['peso']) { $fila['peso'] = floatval($fila['peso']); }
            if ($fila['dorsal']) { $fila['dorsal'] = intval($fila['dorsal']); }
            if ($fila['edad']) { $fila['edad'] = intval($fila['edad']); }
            $jugadores[] = $fila;
        }
        
        echo json_encode($jugadores, JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al filtrar jugadores: ' . $e->getMessage()]);
    } finally {
        if (isset($stmt)) {
            $stmt->close(); 
        }
    }
}

function obtenerJugadores($conn) { // Esta función obtiene TODOS los jugadores
    try {
        // Asegúrate de que 'obtenerJugadores' es el nombre correcto de tu SP
        $stmt = $conn->prepare("CALL obtenerJugadores()");
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $jugadores = [];
        while ($fila = $resultado->fetch_assoc()) {
            // Mantener tus conversiones de tipo si las necesitas
            if ($fila['altura']) { $fila['altura'] = floatval($fila['altura']); }
            if ($fila['peso']) { $fila['peso'] = floatval($fila['peso']); }
            if ($fila['dorsal']) { $fila['dorsal'] = intval($fila['dorsal']); }
            if ($fila['edad']) { $fila['edad'] = intval($fila['edad']); }
            $jugadores[] = $fila;
        }
        
        echo json_encode($jugadores, JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al obtener jugadores: ' . $e->getMessage()]);
    } finally {
        if (isset($stmt)) {
            $stmt->close(); 
        }
    }
}

function registarJugador($conn) { // Corregido: registar a registrar
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) { throw new Exception('No se recibieron datos válidos'); }
        if (empty($input['nombre']) || empty($input['apellido'])) { throw new Exception('El nombre y apellido son obligatorios'); }
        
        $cedula = !empty($input['cedula']) ? $input['cedula'] : null;
        $nombre = trim($input['nombre']);
        $apellido = trim($input['apellido']);
        $fecha_nacimiento = !empty($input['fecha_nacimiento']) ? $input['fecha_nacimiento'] : null;
        $edad = !empty($input['edad']) ? intval($input['edad']) : null;
        $altura = !empty($input['altura']) ? floatval($input['altura']) : null;
        $peso = !empty($input['peso']) ? floatval($input['peso']) : null;
        $telefono = !empty($input['telefono']) ? $input['telefono'] : null;
        $posicion = !empty($input['posicion']) ? $input['posicion'] : null;
        $genero = !empty($input['genero']) ? $input['genero'] : null;
        $dorsal = !empty($input['dorsal']) ? intval($input['dorsal']) : null;
        $fecha_ingreso = !empty($input['fecha_ingreso']) ? $input['fecha_ingreso'] : null;
        $id_categoria = !empty($input['id_categoria']) ? intval($input['id_categoria']) : null;
        
        // Asegúrate de que 'registrarJugador' es el nombre correcto de tu SP
        $stmt = $conn->prepare("CALL registrarJugador (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @resultado, @mensaje, @id_nuevo_jugador)");
        $stmt->bind_param(
            'ssssidddisisi',
            $cedula, $nombre, $apellido, $fecha_nacimiento, $edad, $altura, $peso,
            $telefono, $posicion, $genero, $dorsal, $fecha_ingreso, $id_categoria
        );
        
        if ($stmt->execute()) {
            $stmt->close();
            $result = $conn->query("SELECT @resultado AS resultado, @mensaje AS mensaje, @id_nuevo_jugador AS id_nuevo_jugador");
            $output = $result->fetch_assoc();
            
            if ($output['resultado'] == 1) {
                echo json_encode([
                    'success' => true,
                    'message' => $output['mensaje'],
                    'id_jugador' => intval($output['id_nuevo_jugador'])
                ], JSON_UNESCAPED_UNICODE);
            } else {
                throw new Exception($output['mensaje']);
            }
        } else {
            throw new Exception('Error al ejecutar el procedimiento almacenado');
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
}

function actualizarJugador($conn) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['id_jugador'])) { throw new Exception('ID del jugador es requerido'); }
        
        $id_jugador = intval($input['id_jugador']);
        if (empty($input['nombre']) || empty($input['apellido'])) { throw new Exception('El nombre y apellido son obligatorios'); }
        
        $cedula = !empty($input['cedula']) ? $input['cedula'] : null;
        $nombre = trim($input['nombre']);
        $apellido = trim($input['apellido']);
        $fecha_nacimiento = !empty($input['fecha_nacimiento']) ? $input['fecha_nacimiento'] : null;
        $edad = !empty($input['edad']) ? intval($input['edad']) : null;
        $altura = !empty($input['altura']) ? floatval($input['altura']) : null;
        $peso = !empty($input['peso']) ? floatval($input['peso']) : null;
        $telefono = !empty($input['telefono']) ? $input['telefono'] : null;
        $posicion = !empty($input['posicion']) ? $input['posicion'] : null;
        $genero = !empty($input['genero']) ? $input['genero'] : null;
        $dorsal = !empty($input['dorsal']) ? intval($input['dorsal']) : null;
        $fecha_ingreso = !empty($input['fecha_ingreso']) ? $input['fecha_ingreso'] : null;
        $id_categoria = !empty($input['id_categoria']) ? intval($input['id_categoria']) : null;
        
        // Asegúrate de que 'actualizarJugador' es el nombre correcto de tu SP
        $stmt = $conn->prepare("CALL actualizarJugador(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @resultado, @mensaje)");
        $stmt->bind_param(
            'issssisssssisi',
            $id_jugador, $cedula, $nombre, $apellido, $fecha_nacimiento, $edad, $altura, $peso,
            $telefono, $posicion, $genero, $dorsal, $fecha_ingreso, $id_categoria
        );
        
        if ($stmt->execute()) {
            $stmt->close();
            $result = $conn->query("SELECT @resultado AS resultado, @mensaje AS mensaje");
            $output = $result->fetch_assoc();
            
            if ($output['resultado'] == 1) {
                echo json_encode([
                    'success' => true,
                    'message' => $output['mensaje']
                ], JSON_UNESCAPED_UNICODE);
            } else {
                throw new Exception($output['mensaje']);
            }
        } else {
            throw new Exception('Error al ejecutar el procedimiento almacenado');
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
}

function eliminarJugador($conn) {
    try {
        if (!isset($_GET['id']) || empty($_GET['id'])) { throw new Exception('ID del jugador es requerido'); }
        
        $id_jugador = intval($_GET['id']);
        
        // Asegúrate de que 'eliminarJugador' es el nombre correcto de tu SP
        $stmt = $conn->prepare("CALL eliminarJugador(?, @resultado, @mensaje)");
        $stmt->bind_param('i', $id_jugador);
        
        if ($stmt->execute()) {
            $stmt->close();
            $result = $conn->query("SELECT @resultado AS resultado, @mensaje AS mensaje");
            $output = $result->fetch_assoc();
            
            if ($output['resultado'] == 1) {
                echo json_encode([
                    'success' => true,
                    'message' => $output['mensaje']
                ], JSON_UNESCAPED_UNICODE);
            } else {
                throw new Exception($output['mensaje']);
            }
        } else {
            throw new Exception('Error al ejecutar el procedimiento almacenado');
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
}

// Cerrar la conexión (solo si no se usa jsonResponse o si no se cierra en conexion.php)
// if (isset($conn)) {
//     $conn->close();
// }

// La función jsonResponse debe estar en tu conexion.php o definida aquí
// Si ya la tienes en conexion.php, no la definas aquí para evitar redeclaraciones
/*
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
*/
?>