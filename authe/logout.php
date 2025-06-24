<?php
// authe/logout.php - Script para cerrar la sesión del administrador
session_start(); // Iniciar la sesión para acceder a ella

// Destruir todas las variables de sesión
$_SESSION = array();

// Si se usa una cookie de sesión, también debe ser eliminada.
// Nota: Esto destruirá la sesión, y no solo los datos de sesión.
// Es una buena práctica para asegurar que la sesión se limpia completamente.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destruir la sesión
session_destroy();

// Redirigir a la página de login (index.php en views/)
header('Location: ../views/index.php'); 
exit();
?>