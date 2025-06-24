<?php
session_start(); // Asegurarse de que la sesión esté activa para verificar autenticación

// --- Lógica de Protección de API (Replicar la de tus otras APIs) ---
// Definir APIs/métodos GET que NO requieren autenticación (Informe no debería ser público)
$public_get_allowed_apis = []; // Vacío, ya que el informe es solo para admins logueados
$current_script_name = basename($_SERVER['PHP_SELF']);

if (
    !isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true // No logueado
    && !(
        $_SERVER['REQUEST_METHOD'] === 'GET' && 
        in_array($current_script_name, $public_get_allowed_apis)
    )
) {
    // Si no está autorizado, puedes redirigir al login o simplemente salir
    header('Location: ../views/index.php'); // Redirige al login si no está autorizado
    exit();
}
// --- Fin Lógica de Protección ---

// Incluir archivos necesarios
require_once '../config/conexion.php'; // Tu archivo de conexión a la BD

// Ajusta esta ruta a donde hayas puesto la carpeta tcpdf
require_once '../lib/tcpdf/tcpdf.php'; 

// Obtener parámetros de filtro
$id_categoria = isset($_GET['id_categoria']) ? (int)$_GET['id_categoria'] : 0;
$categoria_nombre_filtro = 'Todas las categorías'; // Para el título del PDF
$entrenador_nombre_filtro = ''; // Para el título del PDF

try {
    // 1. Obtener los datos de los partidos desde la BD (reutilizando obtenerPartidos SP)
    $stmt = $conn->prepare("CALL obtenerPartidos(?)");
    $stmt->bind_param("i", $id_categoria);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $partidos_data = [];
    while ($fila = $resultado->fetch_assoc()) {
        $partidos_data[] = $fila;
    }
    $stmt->close();

    // Si se filtró por una categoría específica, obtener su nombre y el del entrenador
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

    // set default header data
    $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, 'Informe de Partidos - C.D. Momil', "Fecha: " . date('d-m-Y') . "\nCategoría: " . $categoria_nombre_filtro . "\nEntrenador: " . $entrenador_nombre_filtro);

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
        $html .= '<h2 style="text-align: center;">Categoría: ' . $categoria_nombre_filtro . ' (Entrenador: ' . $entrenador_nombre_filtro . ')</h2>';
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
            $html .= '<td>' . $partido['fecha'] . ' ' . substr($partido['hora'], 0, 5) . '</td>';
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
    // Si algo sale mal, redirigir con un mensaje de error o mostrar uno simple
    error_log("Error generando informe de partidos: " . $e->getMessage());
    header('Location: ../views/indexPartidos.php?error_reporte=1'); // Redirigir a la vista de partidos con un error
    exit();
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>