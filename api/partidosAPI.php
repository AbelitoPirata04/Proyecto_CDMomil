<?php
// partidoAPI.php - API para manejar partidos
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS'); // Métodos para CRUD
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/conexion.php'; // Asegúrate de que la ruta es correcta

try {
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            // Lógica para obtener partidos (con o sin filtro de categoría)
            $id_categoria = isset($_GET['id_categoria']) ? $_GET['id_categoria'] : null;
            obtenerPartidos($conn, $id_categoria);
            break;
        case 'POST':
            // Lógica para registrar un nuevo partido
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) {
                jsonResponse(['success' => false, 'message' => 'Datos JSON inválidos'], 400);
            }
            registrarPartido($conn, $data);
            break;
        case 'PUT':
            // Lógica para actualizar un partido existente
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data || !isset($data['id_partido'])) {
                jsonResponse(['success' => false, 'message' => 'Datos JSON inválidos o ID de partido faltante'], 400);
            }
            actualizarPartido($conn, $data);
            break;
        case 'DELETE':
            // Lógica para eliminar un partido
            $id_partido = isset($_GET['id_partido']) ? $_GET['id_partido'] : null;
            if (!$id_partido) {
                jsonResponse(['success' => false, 'message' => 'ID de partido faltante para eliminar'], 400);
            }
            eliminarPartido($conn, $id_partido);
            break;
        default:
            jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
            break;
    }
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()], 500);
} finally {
    // Cerrar la conexión
    if (isset($conn)) {
        $conn->close();
    }
}

function obtenerPartidos($conn, $id_categoria = null) {
    $sql = "CALL obtenerPartidos(?)";

    try {
        $stmt = $conn->prepare($sql);
        
        // Ajuste para el parámetro de categoría si tu SP espera 0 o un entero para "todos"
        $param_categoria = ($id_categoria === null || $id_categoria === '') ? 0 : (int)$id_categoria; // Usar 0 para "todas las categorías"
        $stmt->bind_param("i", $param_categoria); // "i" para entero

        $stmt->execute();
        $resultado = $stmt->get_result();

        $partidos = [];
        while ($fila = $resultado->fetch_assoc()) {
            $partidos[] = $fila;
        }
        jsonResponse($partidos);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => 'Error al obtener partidos: ' . $e->getMessage()], 500);
    }
}

function registrarPartido($conn, $data) {
    if (!isset($data['fecha'], $data['hora'], $data['rival'], $data['local_visitante'], $data['id_categoria'])) {
        jsonResponse(['success' => false, 'message' => 'Faltan datos obligatorios para registrar el partido'], 400);
    }

    $fecha = $data['fecha'];
    $hora = $data['hora'];
    $rival = $data['rival'];
    $local_visitante = $data['local_visitante'];
    $id_categoria = (int)$data['id_categoria']; // Asegurar que sea INT
    
    $goles_favor = isset($data['goles_favor']) ? (int)$data['goles_favor'] : 0;
    $goles_contra = isset($data['goles_contra']) ? (int)$data['goles_contra'] : 0;
    $resultado = "{$goles_favor}-{$goles_contra}"; 

    // Llamar al SP con un parámetro OUT para capturar el ID
    $sql = "CALL registrarPartidos(?, ?, ?, ?, ?, ?, @p_id_partido_insertado)"; 

    try {
        $stmt = $conn->prepare($sql);
        // Bindear los 6 parámetros de entrada (sssssi)
        $stmt->bind_param("sssssi", $fecha, $hora, $rival, $local_visitante, $resultado, $id_categoria);
        
        if ($stmt->execute()) {
            // Después de ejecutar el SP, seleccionar el valor del parámetro OUT
            $result_query = $conn->query("SELECT @p_id_partido_insertado AS id_partido_generado");
            $inserted_id_row = $result_query->fetch_assoc();
            $inserted_id = $inserted_id_row['id_partido_generado'];

            if ($inserted_id === null || $inserted_id == 0) { // En caso de que el SP por algún error devuelva NULL o 0
                jsonResponse(['success' => false, 'message' => 'Partido registrado, pero no se pudo obtener el ID (SP no devolvió ID válido).', 'debug_id' => $inserted_id], 500);
            } else {
                jsonResponse(['success' => true, 'message' => 'Partido registrado exitosamente', 'id_partido' => $inserted_id]);
            }
        } else {
            jsonResponse(['success' => false, 'message' => 'Error al registrar partido: ' . $stmt->error], 500);
        }
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => 'Error al registrar partido: ' . $e->getMessage()], 500);
    }
}


function actualizarPartido($conn, $data) {
    // Validar datos mínimos, incluido el ID
    if (!isset($data['id_partido'], $data['fecha'], $data['hora'], $data['rival'], $data['local_visitante'], $data['id_categoria'])) {
        jsonResponse(['success' => false, 'message' => 'Faltan datos obligatorios para actualizar el partido'], 400);
    }

    $id_partido = $data['id_partido'];
    $fecha = $data['fecha'];
    $hora = $data['hora'];
    $rival = $data['rival'];
    $local_visitante = $data['local_visitante'];
    $id_categoria = $data['id_categoria'];
    
    $goles_favor = isset($data['goles_favor']) ? (int)$data['goles_favor'] : 0;
    $goles_contra = isset($data['goles_contra']) ? (int)$data['goles_contra'] : 0;
    $resultado = "{$goles_favor}-{$goles_contra}";

    // Asumiendo un procedimiento almacenado para actualizar
    $sql = "CALL actualizarPartido(?, ?, ?, ?, ?, ?, ?)"; // id_partido, fecha, hora, rival, local_visitante, resultado, id_categoria

    try {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssssi", $id_partido, $fecha, $hora, $rival, $local_visitante, $resultado, $id_categoria);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                jsonResponse(['success' => true, 'message' => 'Partido actualizado exitosamente']);
            } else {
                jsonResponse(['success' => false, 'message' => 'Partido no encontrado o sin cambios para actualizar'], 404);
            }
        } else {
            jsonResponse(['success' => false, 'message' => 'Error al actualizar partido: ' . $stmt->error], 500);
        }
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => 'Error al actualizar partido: ' . $e->getMessage()], 500);
    }
}

function eliminarPartido($conn, $id_partido) {
    // Asumiendo un procedimiento almacenado para eliminar
    $sql = "CALL eliminarPartido(?)"; // id_partido

    try {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_partido);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                jsonResponse(['success' => true, 'message' => 'Partido eliminado exitosamente']);
            } else {
                jsonResponse(['success' => false, 'message' => 'Partido no encontrado para eliminar'], 404);
            }
        } else {
            jsonResponse(['success' => false, 'message' => 'Error al eliminar partido: ' . $stmt->error], 500);
        }
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => 'Error al eliminar partido: ' . $e->getMessage()], 500);
    }
}
?>