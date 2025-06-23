<?php
// categoriaAPI.php - API para manejar categorías
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/conexion.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        obtenerCategorias($conn);
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}


function obtenerCategorias($conn) {
    try {
        $stmt = $conn->prepare("CALL obtenerCategorias()");
        $stmt->execute();
        $resultado = $stmt->get_result();

        $categorias = [];

        while ($fila = $resultado->fetch_assoc()) {
            $categorias[] = $fila;
        }

        echo json_encode($categorias);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al obtener categorías: ' . $e->getMessage()]);
    }
}

// Cerrar la conexión
if (isset($conn)) {
    $conn->close();
}
?>