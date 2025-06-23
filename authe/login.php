<?php
include("../config/conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';

    if (empty($usuario) || empty($contrasena)) {
        echo "<script>alert('Por favor, completa todos los campos'); window.location.href='../views/index.php';</script>";
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM administradores WHERE usuario = ? AND contrasena = ?");
    $stmt->bind_param("ss", $usuario, $contrasena);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado && $resultado->num_rows > 0) {
        header("Location: ../views/indexAdmin.php");
        exit();
    } else {
        echo "<script>alert('Usuario o contraseña incorrectos'); window.location.href='../views/index.php';</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
