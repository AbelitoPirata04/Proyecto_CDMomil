<?php
session_start(); // Iniciar sesión al principio de cada API

// Definir APIs/métodos GET que NO requieren autenticación (el informe no debería ser público)
$public_get_allowed_apis = [
    // 'categoriaAPI.php', // Descomentar si necesitas categorías para el login o algo público
    // 'estadisticasAPI.php' // Descomentar si la vista de estadísticas fuera pública sin login
];

$current_script_name = basename($_SERVER['PHP_SELF']);

// Regla de Protección:
// Si la sesión no está iniciada O el método no es GET y no es una API permitida públicamente
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
            if (isset($_GET['id_partido_detalle'])) { 
                obtenerDetallePartidoAPI($conn, $_GET['id_partido_detalle']);
            } 
            // NUEVO: Manejar petición para informe PDF (TCPDF)
            elseif (isset($_GET['get_for_report'])) { 
                $id_categoria_reporte = isset($_GET['id_categoria']) ? $_GET['id_categoria'] : 0;
                obtenerPartidosParaInformeAPI($conn, $id_categoria_reporte); // Esta función NO devuelve JSON, genera el PDF directamente.
            }
            // Lógica para filtrar partidos por categoría (vista normal)
            elseif (isset($_GET['id_categoria'])) { 
                obtenerPartidos($conn, $_GET['id_categoria']);
            } 
            // Lógica para obtener todos los partidos (vista normal)
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

// --- Funciones existentes (Mantenerlas tal cual como funcionan) ---

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

// ¡IMPORTANTE! Esta función ya NO DEVUELVE JSON. Genera el PDF directamente con TCPDF.
function obtenerPartidosParaInformeAPI($conn, $id_categoria) {
    // Replicar la lógica de protección de sesión aquí si se desea que la descarga de PDF sea muy estricta
    // y no solo verificar al inicio del script.
    
    // Incluir la librería TCPDF
    // ¡AJUSTA ESTA RUTA si tu carpeta 'tcpdf' no está directamente en 'lib/'!
    require_once '../lib/tcpdf/tcpdf.php'; 

    try {
        // Obtener los datos de los partidos desde la BD
        $stmt = $conn->prepare("CALL obtenerPartidos(?)"); // Reutilizamos el mismo SP 'obtenerPartidos'
        $stmt->bind_param("i", $id_categoria);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $partidos_data = [];
        while ($fila = $resultado->fetch_assoc()) {
            $partidos_data[] = $fila;
        }
        $stmt->close();

        // Si se filtró por una categoría específica, obtener su nombre y el del entrenador
        $categoria_nombre_filtro = 'Todas las categorías';
        $entrenador_nombre_filtro = '';
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

        // 2. Generar el PDF con TCPDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('C.D. Momil Administrador');
        $pdf->SetTitle('Informe de Partidos');
        $pdf->SetSubject('Listado de Partidos');

        // set default header data (Asegúrate de que 'logoCD.png' esté en la ruta correcta para TCPDF)
        $logo_path = '../recursos/img/logoCD.png'; // Ruta relativa desde este PHP a tu imagen
        if (!file_exists($logo_path)) {
            // Fallback si la ruta relativa no funciona en el servidor para TCPDF
            $logo_path = $_SERVER['DOCUMENT_ROOT'] . '/Proyecto_CDMomil/recursos/img/logoCD.png'; 
            // ¡AJUSTA '/Proyecto_CDMomil/' a la raíz de tu proyecto web si es diferente!
            if (!file_exists($logo_path)) {
                $logo_path = ''; // No logo if not found
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

        // Contenido HTML para la tabla del PDF
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

        // Close and output PDF document
        $pdf->Output('informe_partidos.pdf', 'D'); // 'D' = Descargar el archivo

    } catch (Exception $e) {
        error_log("Error generando informe de partidos (TCPDF): " . $e->getMessage());
        // Puedes guardar el mensaje en sesión para mostrarlo en el frontend
        $_SESSION['report_error'] = 'Error al generar el informe: ' . $e->getMessage();
        header('Location: ../views/indexPartidos.php'); // Redirigir a la vista de partidos
        exit();
    }
}
?>