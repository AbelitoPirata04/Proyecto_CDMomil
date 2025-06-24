<?php
session_start(); // Iniciar sesión al principio de cada API

// Definir APIs/métodos GET que NO requieren autenticación (la mayoría de tu panel no deberían ser públicos)
$public_get_allowed_apis = [
    // Si necesitas que categoriaAPI.php sea accesible sin login, añádelo aquí
    // 'categoriaAPI.php', 
    // Si la vista de estadísticas fuera pública sin login, podrías poner 'estadisticasAPI.php' aquí
];

$current_script_name = basename($_SERVER['PHP_SELF']);

// Regla de Protección:
// Si la sesión no está iniciada
// Y la petición NO es un GET a una API permitida públicamente
if (
    !isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true // No logueado
    && !(
        $_SERVER['REQUEST_METHOD'] === 'GET' && 
        in_array($current_script_name, $public_get_allowed_apis)
    )
) {
    http_response_code(401); // 401 Unauthorized
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado. Inicia sesión para realizar esta acción.']);
    exit();
}

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
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            // Nuevo: Si se envía un id_partido_detalle, obtener sus detalles
            if (isset($_GET['id_partido_detalle'])) { 
                obtenerDetallePartidoAPI($conn, $_GET['id_partido_detalle']);
            } 
            // Lógica existente para obtener partidos (con o sin filtro de categoría)
            elseif (isset($_GET['id_categoria'])) { 
                obtenerPartidos($conn, $_GET['id_categoria']);
            } 
            else { 
                obtenerPartidos($conn);
            }
            break;
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) {
                jsonResponse(['success' => false, 'message' => 'Datos JSON inválidos'], 400);
            }
            registrarPartido($conn, $data); 
            break;
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data || !isset($data['id_partido'])) {
                jsonResponse(['success' => false, 'message' => 'Datos JSON inválidos o ID de partido faltante'], 400);
            }
            actualizarPartido($conn, $data);
            break;
        case 'DELETE':
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
    if (isset($conn)) {
        $conn->close();
    }
}

function obtenerPartidos($conn, $id_categoria = null) {
    $sql = "CALL obtenerPartidos(?)";

    try {
        $stmt = $conn->prepare($sql);
        
        $param_categoria = ($id_categoria === null || $id_categoria === '') ? 0 : (int)$id_categoria; 
        $stmt->bind_param("i", $param_categoria); 

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
    $id_categoria = (int)$data['id_categoria']; 
    
    $goles_favor = isset($data['goles_favor']) ? (int)$data['goles_favor'] : 0;
    $goles_contra = isset($data['goles_contra']) ? (int)$data['goles_contra'] : 0;
    $resultado = "{$goles_favor}-{$goles_contra}"; 

    $sql = "CALL registrarPartidos(?, ?, ?, ?, ?, ?, @p_id_partido_insertado)"; 

    try {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $fecha, $hora, $rival, $local_visitante, $resultado, $id_categoria);
        
        if ($stmt->execute()) {
            $result_query = $conn->query("SELECT @p_id_partido_insertado AS id_partido_generado");
            $inserted_id_row = $result_query->fetch_assoc();
            $inserted_id = $inserted_id_row['id_partido_generado'];

            if ($inserted_id === null || $inserted_id == 0) { 
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

    $sql = "CALL actualizarPartido(?, ?, ?, ?, ?, ?, ?)"; 

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
    $sql = "CALL eliminarPartido(?)"; 

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

// ¡NUEVA FUNCIÓN! Para obtener los detalles de un partido con sus jugadores
function obtenerDetallePartidoAPI($conn, $id_partido) {
    try {
        $stmt = $conn->prepare("CALL obtenerDetallesPartidoConJugadores(?)");
        $stmt->bind_param("i", $id_partido);
        $stmt->execute();

        // Primer conjunto de resultados: Detalles del Partido
        // Usamos store_result() para asegurar que podemos movernos a next_result()
        $stmt->store_result(); 
        $partido_detalle = [];
        $meta = $stmt->result_metadata();
        $fields = [];
        foreach ($meta->fetch_fields() as $field) {
            $fields[] = &$partido_detalle[$field->name];
        }
        call_user_func_array([$stmt, 'bind_result'], $fields);
        $stmt->fetch(); // Obtener la primera (y única) fila del partido_detalle
        $stmt->free_result(); // Liberar el primer conjunto de resultados

        $stmt->next_result(); // Mover al siguiente conjunto de resultados

        // Segundo conjunto de resultados: Estadísticas de Jugadores
        $jugadores_stats = [];
        $result_jugadores = $stmt->get_result(); // get_result() es más sencillo después de next_result()

        while ($fila = $result_jugadores->fetch_assoc()) {
            $jugadores_stats[] = $fila;
        }
        $stmt->close(); // Cerrar el statement después de obtener todos los resultados

        if ($partido_detalle) {
            jsonResponse([
                'success' => true,
                'partido' => $partido_detalle,
                'jugadores_stats' => $jugadores_stats
            ]);
        } else {
            jsonResponse(['success' => false, 'message' => 'Partido no encontrado'], 404);
        }
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => 'Error al obtener detalles del partido: ' . $e->getMessage()], 500);
    }
}
?>