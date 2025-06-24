<?php
session_start(); // Iniciar sesión al principio del script

include("../config/conexion.php"); // Tu archivo de conexión a la BD

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';

    if (empty($usuario) || empty($contrasena)) {
        $_SESSION['login_error'] = 'Por favor, completa todos los campos.';
        header('Location: ../views/index.php'); // Redirigir de vuelta al login
        exit();
    }

    // Preparar la consulta. Si tus contraseñas en DB están hasheadas, USA password_verify()
    // En tu BD, algunas están hasheadas ($2y$...) y otras en texto plano.
    // LO MÁS SEGURO ES HASHEAR TODAS Y USAR password_verify().
    // Para esta implementación, asumo que TODAS tus contraseñas en 'administradores' están hasheadas con bcrypt.
    // Si no lo están, cámbialo a la línea comentada (menos segura).
    $stmt = $conn->prepare("SELECT id_admin, usuario, contrasena FROM administradores WHERE usuario = ? LIMIT 1");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $admin = $resultado->fetch_assoc(); // Obtener la fila del administrador

    $stmt->close();
    $conn->close();

    if ($admin && password_verify($contrasena, $admin['contrasena'])) { // Si usas contraseñas hasheadas
    // if ($admin && $contrasena === $admin['contrasena']) { // Si tus contraseñas son texto plano
        $_SESSION['admin_logged_in'] = true; // Marca la sesión como logueada
        $_SESSION['admin_id'] = $admin['id_admin']; // Guarda el ID del admin
        $_SESSION['admin_usuario'] = $admin['usuario']; // Guarda el usuario del admin

        header("Location: ../views/indexAdmin.php"); // Redirigir al dashboard
        exit();
    } else {
        $_SESSION['login_error'] = 'Usuario o contraseña incorrectos.';
        header('Location: ../views/index.php'); // Redirigir de vuelta al login con error
        exit();
    }
} else {
    // Si alguien intenta acceder a login.php directamente sin POST, redirigir al login
    header('Location: ../views/index.php');
    exit();
}
?>