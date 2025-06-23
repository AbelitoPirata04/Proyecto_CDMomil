<?php
// estadisticasAPI.php - API para obtener estadísticas acumuladas de jugadores
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/conexion.php'; // Asegúrate de que la ruta es correcta

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $id_categoria = isset($_GET['id_categoria']) ? $_GET['id_categoria'] : 0; // 0 para todas
        $ordenar_por = isset($_GET['ordenar_por']) ? $_GET['ordenar_por'] : ''; // Campo para ordenar

        obtenerEstadisticasJugadoresAPI($conn, $id_categoria, $ordenar_por);
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}

function obtenerEstadisticasJugadoresAPI($conn, $id_categoria, $ordenar_por) {
    try {
        $stmt = $conn->prepare("CALL obtenerEstadisticasJugadores(?, ?)");
        $stmt->bind_param("is", $id_categoria, $ordenar_por);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $estadisticas = [];
        while ($fila = $resultado->fetch_assoc()) {
            // Convertir valores numéricos si es necesario (ya el SP usa COALESCE)
            $fila['goles_totales'] = (int)$fila['goles_totales'];
            $fila['partidos_jugados'] = (int)$fila['partidos_jugados'];
            $fila['minutos_jugados'] = (int)$fila['minutos_jugados'];
            $fila['tarjetas_amarillas'] = (int)$fila['tarjetas_amarillas'];
            $fila['tarjetas_rojas'] = (int)$fila['tarjetas_rojas'];
            $estadisticas[] = $fila;
        }
        echo json_encode($estadisticas, JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al obtener estadísticas de jugadores: ' . $e->getMessage()]);
    }
}
?>