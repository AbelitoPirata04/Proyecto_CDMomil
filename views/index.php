<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Inicio de Sesión - C.D. Momil</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex h-screen">

  <!-- Imagen de fondo -->
  <div class="w-[65%] h-full bg-cover bg-center" 
       style="background-image: url('../recursos/img/FondoLogin.jpg')">
  </div>
  
  <!-- Panel de login -->
  <div class="w-[35%] bg-green-900 flex flex-col items-center justify-center px-8">
    
    <!-- Logo -->
    <img src="../recursos/img/logoCD.png" alt="Logo CD Momil" class="w-44 h-44 mb-6" />

    <!-- Formulario -->
    <form class="w-full max-w-sm" method="POST" action="../authe/login.php">
      
      <!-- Usuario -->
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

      <!-- Contraseña -->
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

      <!-- Botón -->
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
