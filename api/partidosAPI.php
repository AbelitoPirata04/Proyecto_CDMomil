<?php
session_start();

// Definir APIs/métodos GET que NO requieren autenticación (el informe no debería ser público)
$public_get_allowed_apis = [
    // Si alguna API GET específica NECESITA ser pública sin login, añádela aquí.
    // 'categoriaAPI.php',
    // 'estadisticasAPI.php'
];

$current_script_name = basename($_SERVER['PHP_SELF']);

// Regla de Protección:
if (
    !isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true
    && !(
        $_SERVER['REQUEST_METHOD'] === 'GET' &&
        in_array($current_script_name, $public_get_allowed_apis)
    )
) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado. Inicia sesión para realizar esta acción.']);
    exit();
}

// Estos encabezados deben estar ANTES de cualquier salida.
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Incluir el archivo de conexión. Si falla la conexión, lanzará una excepción.
require_once '../config/conexion.php';

// --- Función para enviar respuestas JSON ---
// Esta función se define aquí porque este es un script API que produce JSON.
function jsonResponse($data, $statusCode = 200) {
    // Asegurarse de que el Content-Type sea JSON para estas respuestas.
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE); // JSON_UNESCAPED_UNICODE para caracteres especiales
    exit(); // ¡Importante! Detener la ejecución después de enviar la respuesta JSON.
}
// --- Fin Función para enviar respuestas JSON ---


try {
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            if (isset($_GET['id_partido_detalle'])) {
                obtenerDetallePartidoAPI($conn, $_GET['id_partido_detalle']);
            }
            elseif (isset($_GET['get_for_report'])) {
                $id_categoria_reporte = isset($_GET['id_categoria']) && $_GET['id_categoria'] !== '' ? (int)$_GET['id_categoria'] : 0;
                obtenerPartidosParaInformeAPI($conn, $id_categoria_reporte);
                // La función obtenerPartidosParaInformeAPI ya envía el PDF y hace exit().
            }
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
            $id_partido_url = isset($_GET['id_partido']) ? $_GET['id_partido'] : null;
            $data = json_decode(file_get_contents('php://input'), true);

            // Prioriza el ID de la URL, si no está, busca en el cuerpo.
            if (!$id_partido_url && isset($data['id_partido'])) {
                $id_partido_url = $data['id_partido'];
            }

            if (!$data || !$id_partido_url) {
                jsonResponse(['success' => false, 'message' => 'Datos JSON inválidos o ID de partido faltante para actualizar'], 400);
            }
            $data['id_partido'] = $id_partido_url; // Asegura que el ID esté en los datos para la función
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
    // Si cualquier función lanza una excepción (incluida la de conexión),
    // la capturamos aquí y respondemos con un JSON de error.
    jsonResponse(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()], 500);
} finally {
    // En scripts API que usan `exit()` después de `jsonResponse()` o `pdf->Output()`,
    // el `$conn->close()` se realiza automáticamente al finalizar el script.
    // Un `finally` explícito aquí no es estrictamente necesario y podría causar problemas
    // si el script ya terminó antes.
}

// --- Funciones de la API ---

function obtenerPartidos($conn, $id_categoria = null) {
    $sql = "CALL obtenerPartidos(?)"; // Suponemos que este SP filtra por 0 para todas las categorías.

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

function obtenerDetallePartidoAPI($conn, $id_partido) {
    $partido_id = (int)$id_partido;

    // Consulta para obtener detalles del partido
    $sql_partido = "SELECT p.*, c.nombre AS categoria_nombre
                    FROM partido p
                    JOIN categoria c ON p.id_categoria = c.id_categoria
                    WHERE p.id_partido = ?";
    $stmt_partido = $conn->prepare($sql_partido);
    $stmt_partido->bind_param("i", $partido_id);
    $stmt_partido->execute();
    $result_partido = $stmt_partido->get_result();
    $partido_data = $result_partido->fetch_assoc();
    $stmt_partido->close();

    if (!$partido_data) {
        jsonResponse(['success' => false, 'message' => 'Partido no encontrado.'], 404);
    }

    // Consulta para obtener estadísticas de jugadores para ese partido
    $sql_jugadores = "SELECT jp.*, j.nombre, j.apellido, j.dorsal, j.posicion
                      FROM jugador_partido jp
                      JOIN jugador j ON jp.id_jugador = j.id_jugador
                      WHERE jp.id_partido = ?";
    $stmt_jugadores = $conn->prepare($sql_jugadores);
    $stmt_jugadores->bind_param("i", $partido_id);
    $stmt_jugadores->execute();
    $result_jugadores = $stmt_jugadores->get_result();
    $jugadores_stats = [];
    while ($row = $result_jugadores->fetch_assoc()) {
        $jugadores_stats[] = $row;
    }
    $stmt_jugadores->close();

    jsonResponse([
        'success' => true,
        'partido' => $partido_data,
        'jugadores_stats' => $jugadores_stats
    ]);
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

function obtenerPartidosParaInformeAPI($conn, $id_categoria) {
    require_once '../lib/tcpdf/tcpdf.php';

    try {
        $stmt = $conn->prepare("CALL obtenerPartidos(?)");
        $stmt->bind_param("i", $id_categoria);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $partidos_data = [];
        while ($fila = $resultado->fetch_assoc()) {
            $partidos_data[] = $fila;
        }
        $stmt->close();

        $categoria_nombre_filtro = 'Todas las categorías';
        $entrenador_nombre_filtro = 'N/A';
        if ($id_categoria != 0) {
            $stmt_cat = $conn->prepare("SELECT c.nombre, COALESCE(CONCAT(e.nombre, ' ', e.apellido), 'Sin Entrenador') AS entrenador_nombre
                                         FROM categoria c LEFT JOIN entrenador e ON c.id_entrenador = e.id_entrenador
                                         WHERE c.id_categoria = ? LIMIT 1");
            $stmt_cat->bind_param("i", $id_categoria);
            $stmt_cat->execute();
            $cat_info = $stmt_cat->get_result()->fetch_assoc();
            if ($cat_info) {
                $categoria_nombre_filtro = $cat_info['nombre'];
                $entrenador_nombre_filtro = $cat_info['entrenador_nombre'];
            }
            $stmt_cat->close();
        }

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('C.D. Momil Administrador');
        $pdf->SetTitle('Informe de Partidos');
        $pdf->SetSubject('Listado de Partidos');

        $logo_path = '../recursos/img/logoCD.png';
        if (!file_exists($logo_path)) {
            // Intenta con una ruta absoluta de fallback, ajusta si es necesario
            $logo_path = $_SERVER['DOCUMENT_ROOT'] . '/Proyecto_CDMomil/recursos/img/logoCD.png';
            if (!file_exists($logo_path)) {
                $logo_path = ''; // No logo if not found
                error_log("ADVERTENCIA: No se encontró el logo en " . realpath('../recursos/img/logoCD.png') . " ni en " . $_SERVER['DOCUMENT_ROOT'] . "/Proyecto_CDMomil/recursos/img/logoCD.png");
            }
        }

        $pdf->SetHeaderData($logo_path, 20, 'Informe de Partidos - C.D. Momil', "Fecha: " . date('d-m-Y') . "\nCategoría: " . $categoria_nombre_filtro . "\nEntrenador: " . $entrenador_nombre_filtro);

        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        $pdf->SetFont('helvetica', '', 10);

        $pdf->AddPage();

        $html = '<h1 style="text-align: center;">Listado de Partidos del Club Deportivo Momil</h1>';
        if ($id_categoria != 0) {
            $html .= '<h2 style="text-align: center;">Categoría: ' . htmlspecialchars($categoria_nombre_filtro) . ' (Entrenador: ' . htmlspecialchars($entrenador_nombre_filtro) . ')</h2>';
        }
        $html .= '<br><table border="1" cellpadding="5" cellspacing="0" style="width:100%; border-collapse: collapse;">
                    <tr style="background-color:#E0E0E0;">
                        <th>Fecha y Hora</th>
                        <th>Rival</th>
                        <th>Localía</th>
                        <th>Resultado</th>
                        <th>Categoría</th>
                    </tr>';

        if (empty($partidos_data)) {
            $html .= '<tr><td colspan="5" style="text-align:center;">No hay partidos para los filtros seleccionados.</td></tr>';
        } else {
            foreach ($partidos_data as $partido) {
                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars($partido['fecha']) . ' ' . htmlspecialchars(substr($partido['hora'], 0, 5)) . '</td>';
                $html .= '<td>' . htmlspecialchars($partido['rival']) . '</td>';
                $html .= '<td>' . htmlspecialchars($partido['local_visitante']) . '</td>';
                $html .= '<td>' . htmlspecialchars($partido['resultado']) . '</td>';
                $html .= '<td>' . htmlspecialchars($partido['categoria_nombre']) . '</td>';
                $html .= '</tr>';
            }
        }

        $html .= '</table>';

        $pdf->writeHTML($html, true, false, true, false, '');

        $pdf->Output('informe_partidos.pdf', 'D'); // 'D' = Descargar el archivo
        exit(); // Crucial para detener la ejecución del script PHP después de enviar el PDF.

    } catch (Exception $e) {
        // En caso de error durante la generación del PDF con TCPDF
        error_log("Error generando informe de partidos (TCPDF): " . $e->getMessage());
        // No se puede enviar una respuesta JSON aquí porque los encabezados HTTP ya se manipularon para el PDF.
        // El cliente simplemente no obtendrá el PDF o verá un PDF corrupto.
        exit();
    }
}