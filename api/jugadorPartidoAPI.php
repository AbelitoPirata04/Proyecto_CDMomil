<?php
session_start();

// --- Función para enviar respuestas JSON ---
// Esta función DEBE estar definida LO MÁS ARRIBA POSIBLE
// en este script, ya que es el que la va a utilizar.
function jsonResponse($data, $statusCode = 200) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit(); // Crucial para detener la ejecución y evitar salidas adicionales.
}
// --- Fin Función para enviar respuestas JSON ---

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS'); // Solo POST para guardar múltiples stats
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Lógica de autenticación (similar a otros APIs)
// Asegúrate de que esta API está protegida si debe serlo.
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    jsonResponse(['success' => false, 'message' => 'Acceso no autorizado. Inicia sesión para realizar esta acción.'], 401);
}

// INICIO DEL BLOQUE TRY PRINCIPAL PARA MANEJAR ERRORES DE CONEXIÓN O EJECUCIÓN
try {
    require_once '../config/conexion.php'; // Incluir conexión dentro del try

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!is_array($data)) {
            jsonResponse(['success' => false, 'message' => 'Datos JSON inválidos o no es un array de estadísticas.'], 400);
        }

        // Si no se envía ninguna estadística, se asume que no hay nada que guardar.
        // Podría ser un escenario válido si un partido no tuvo jugadores con stats.
        if (empty($data)) {
            jsonResponse(['success' => true, 'message' => 'No se proporcionaron estadísticas de jugadores para guardar.']);
            return; // Termina la ejecución aquí
        }

        // Iniciar transacción para asegurar que todas las inserciones/actualizaciones se completen o ninguna
        $conn->begin_transaction();
        $errores = [];
        $id_partido_actual = null;

        // Extraer el id_partido del primer elemento para la eliminación previa
        if (!empty($data) && isset($data[0]['id_partido'])) {
            $id_partido_actual = (int)$data[0]['id_partido'];
        } else {
            // Error si no se puede determinar el ID del partido
            jsonResponse(['success' => false, 'message' => 'No se pudo determinar el ID del partido para guardar estadísticas.'], 400);
        }

        // Paso 1: Eliminar todas las estadísticas existentes para este partido.
        // Esto es crucial para la lógica de "editar" estadísticas: borrar viejas e insertar nuevas.
        $deleteSql = "DELETE FROM jugador_partido WHERE id_partido = ?";
        $deleteStmt = $conn->prepare($deleteSql);
        if (!$deleteStmt) {
            $conn->rollback(); // Revertir si la preparación falla
            throw new Exception("Fallo en la preparación para eliminar estadísticas anteriores: " . $conn->error);
        }
        $deleteStmt->bind_param("i", $id_partido_actual);
        if (!$deleteStmt->execute()) {
            $conn->rollback(); // Revertir si la ejecución falla
            throw new Exception("Fallo al eliminar estadísticas anteriores: " . $deleteStmt->error);
        }
        $deleteStmt->close();

        // Paso 2: Insertar las nuevas estadísticas
        $insertedCount = 0;
        foreach ($data as $stat) {
            // Validar datos mínimos por cada estadística
            if (!isset($stat['id_jugador'], $stat['id_partido'])) {
                $errores[] = 'Estadística incompleta para un jugador (id_jugador o id_partido faltante).';
                continue; // Saltar a la siguiente estadística
            }

            $id_jugador = (int)$stat['id_jugador'];
            $id_partido = (int)$stat['id_partido']; // Ya lo tenemos del primero, pero lo reconfirmamos
            $minutos_jugados = isset($stat['minutos_jugados']) ? (int)$stat['minutos_jugados'] : 0;
            $goles = isset($stat['goles']) ? (int)$stat['goles'] : 0;
            $tarjetas_amarillas = isset($stat['tarjetas_amarillas']) ? (int)$stat['tarjetas_amarillas'] : 0;
            $tarjetas_rojas = isset($stat['tarjetas_rojas']) ? (int)$stat['tarjetas_rojas'] : 0;

            // Llama al SP para insertar o actualizar (si el SP lo hace)
            // O un INSERT simple si ya hiciste DELETE antes.
            // Si el SP 'insertarActualizarJugadorPartido' maneja INSERT/UPDATE, está bien.
            // Si es solo para INSERT, entonces el DELETE previo es la clave.
            $sql = "CALL insertarActualizarJugadorPartido(?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                $errores[] = "Fallo en la preparación de inserción para jugador {$id_jugador}: " . $conn->error;
                continue; // Saltar a la siguiente estadística
            }
            $stmt->bind_param("iiiiii", $id_jugador, $id_partido, $minutos_jugados, $goles, $tarjetas_amarillas, $tarjetas_rojas);

            if ($stmt->execute()) {
                $insertedCount++;
            } else {
                $errores[] = "Error al procesar estadísticas para jugador {$id_jugador} en partido {$id_partido}: " . $stmt->error;
            }
            $stmt->close(); // Cerrar el statement en cada iteración
        }

        if (empty($errores)) {
            $conn->commit(); // Si no hay errores, confirmar la transacción
            jsonResponse(['success' => true, 'message' => "Estadísticas guardadas exitosamente. Registros procesados: $insertedCount."]);
        } else {
            $conn->rollback(); // Si hay errores, revertir la transacción
            jsonResponse(['success' => false, 'message' => 'Errores al guardar algunas estadísticas. No se guardó nada en esta operación.', 'details' => $errores], 500);
        }

    } else {
        jsonResponse(['success' => false, 'message' => 'Método no permitido.'], 405);
    }

} catch (Exception $e) {
    // Este catch captura errores fatales, como el fallo de conexión de `conexion.php`.
    // Asegurar que la transacción se revierta en caso de cualquier excepción general.
    if (isset($conn) && $conn->in_transaction) {
        $conn->rollback();
    }
    jsonResponse(['success' => false, 'message' => 'Error fatal del servidor al guardar estadísticas: ' . $e->getMessage()], 500);
} finally {
    // La conexión se cierra automáticamente al finalizar el script si jsonResponse() hace exit().
    // Esto es más para asegurar que se cierra si hay una ruta de ejecución sin jsonResponse (que no debería haber).
    if (isset($conn) && is_object($conn)) {
        $conn->close();
    }
}
?>