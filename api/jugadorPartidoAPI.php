<?php
// jugadorPartidoAPI.php - API para manejar estadísticas de jugador por partido
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: POST, OPTIONS'); // Solo POST para guardar múltiples stats
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/conexion.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!is_array($data)) {
            jsonResponse(['success' => false, 'message' => 'Datos JSON inválidos o no es un array de estadísticas.'], 400);
        }

        // Iniciar transacción para asegurar que todas las inserciones/actualizaciones se completen o ninguna
        $conn->begin_transaction();
        $errores = [];

        foreach ($data as $stat) {
            // Validar datos mínimos por cada estadística
            if (!isset($stat['id_jugador'], $stat['id_partido'])) {
                $errores[] = 'Estadística incompleta para un jugador.';
                continue;
            }

            $id_jugador = $stat['id_jugador'];
            $id_partido = $stat['id_partido'];
            $minutos_jugados = isset($stat['minutos_jugados']) ? (int)$stat['minutos_jugados'] : 0;
            $goles = isset($stat['goles']) ? (int)$stat['goles'] : 0;
            $tarjetas_amarillas = isset($stat['tarjetas_amarillas']) ? (int)$stat['tarjetas_amarillas'] : 0;
            $tarjetas_rojas = isset($stat['tarjetas_rojas']) ? (int)$stat['tarjetas_rojas'] : 0;

            // Llama al SP para insertar o actualizar
            // Este SP se encargará de determinar si inserta o actualiza
            $sql = "CALL insertarActualizarJugadorPartido(?, ?, ?, ?, ?, ?)"; 

            $stmt = $conn->prepare($sql);
            // Tipos de parámetros: i = int
            $stmt->bind_param("iiiiii", $id_jugador, $id_partido, $minutos_jugados, $goles, $tarjetas_amarillas, $tarjetas_rojas);

            if (!$stmt->execute()) {
                $errores[] = "Error al procesar estadísticas para jugador {$id_jugador} en partido {$id_partido}: " . $stmt->error;
            }
            $stmt->close(); // Cerrar el statement en cada iteración
        }

        if (empty($errores)) {
            $conn->commit(); // Si no hay errores, confirmar la transacción
            jsonResponse(['success' => true, 'message' => 'Estadísticas guardadas exitosamente.']);
        } else {
            $conn->rollback(); // Si hay errores, revertir la transacción
            jsonResponse(['success' => false, 'message' => 'Errores al guardar algunas estadísticas.', 'details' => $errores], 500);
        }

    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    }
} catch (Exception $e) {
    $conn->rollback(); // Asegurar rollback en caso de excepción general
    jsonResponse(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()], 500);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>