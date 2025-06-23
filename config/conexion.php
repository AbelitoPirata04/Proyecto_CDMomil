<?php
// config.php - Configuración de la base de datos con MySQLi
$host = "localhost";
$usuario = "root";
$contrasena = "";
$base_datos = "cdmomil";

// Crear conexión
$conn = new mysqli($host, $usuario, $contrasena, $base_datos);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Establecer charset para caracteres especiales
$conn->set_charset("utf8mb4");

// Función para responder con JSON
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
?>