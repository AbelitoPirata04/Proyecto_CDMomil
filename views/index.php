<?php
session_start(); // Iniciar sesión PHP al principio del archivo

// Si el usuario ya está logueado, redirigir al panel principal
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: indexAdmin.php'); // Redirige al dashboard si ya está logueado
    exit();
}

$error_message = '';
// Si hay un mensaje de error en la sesión (de authe/login.php), mostrarlo
if (isset($_SESSION['login_error'])) {
    $error_message = $_SESSION['login_error'];
    unset($_SESSION['login_error']); // Limpiar el mensaje después de mostrarlo
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Inicio de Sesión - C.D. Momil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../recursos/css/style.css"> </head>
<body class="flex h-screen">

    <div class="w-[65%] h-full bg-cover bg-center" 
        style="background-image: url('../recursos/img/FondoLogin.jpg')">
    </div>
    
    <div class="w-[35%] bg-green-900 flex flex-col items-center justify-center px-8">
        
        <img src="../recursos/img/logoCD.png" alt="Logo CD Momil" class="w-44 h-44 mb-6" />

        <form class="w-full max-w-sm" method="POST" action="../authe/login.php">
            
            <?php if ($error_message): // Mostrar mensaje de error si existe ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Error:</strong>
                    <span class="block sm:inline"><?php echo htmlspecialchars($error_message); ?></span>
                </div>
            <?php endif; ?>

            <div class="mb-4">
                <label class="block text-black text-sm font-bold mb-2" for="usuario">Usuario</label>
                <input 
                    name="usuario"
                    id="usuario"
                    type="text"
                    placeholder="Ingresa tu usuario"
                    class="w-full px-4 py-2 bg-yellow-600 text-white rounded focus:outline-none focus:ring-2 focus:ring-yellow-300 placeholder-white"
                    required
                >
            </div>

            <div class="mb-6">
                <label class="block text-black text-sm font-bold mb-2" for="contrasena">Contraseña</label>
                <input 
                    name="contrasena"
                    id="contrasena"
                    type="password"
                    placeholder="Ingresa tu contraseña"
                    class="w-full px-4 py-2 bg-yellow-600 text-white rounded focus:outline-none focus:ring-2 focus:ring-yellow-300 placeholder-white"
                    required
                >
            </div>

            <div class="flex items-center justify-center">
                <button 
                    type="submit"
                    class="bg-red-800 hover:bg-red-700 text-white font-bold py-2 px-4 rounded w-full transition"
                >
                    Iniciar Sesión
                </button>
            </div>
        </form>
    </div>

</body>
</html>