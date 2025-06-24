<?php

$host = "localhost";
$usuario = "root";
$contrasena = "";
$base_datos = "cdmomil";

// Crear conexión
$conn = new mysqli($host, $usuario, $contrasena, $base_datos);

// Verificar conexión
if ($conn->connect_error) {
    // En lugar de die() con salida HTML, lanzamos una excepción.
    // Esto permite que el script que incluye conexion.php (ej. partidosAPI.php)
    // capture este error y lo maneje adecuadamente (ej. respondiendo con JSON de error).
    throw new Exception("Error de conexión a la base de datos: " . $conn->connect_error);
}

// Establecer charset para caracteres especiales
$conn->set_charset("utf8mb4");

// NOTA: La función jsonResponse se ha movido a partidosAPI.php y otros archivos API
// para garantizar que los encabezados HTTP se envíen correctamente en el contexto del API.
// No debe estar aquí para evitar conflictos o salidas prematuras.