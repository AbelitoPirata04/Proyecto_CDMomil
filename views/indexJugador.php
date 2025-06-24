<?php
session_start(); // Iniciar sesión en cada página protegida

// Verificar si el administrador no está logueado
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Si no está logueado, redirigir a la página de login
    header('Location: index.php'); // Asume que login.php está en la misma carpeta views/
    exit();
}

// Opcional: Obtener datos del admin para mostrar en el header
$admin_usuario = $_SESSION['admin_usuario'] ?? 'Administrador'; 
// Puedes usar $admin_id = $_SESSION['admin_id']; si lo necesitas.
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Jugadores - C.D. Momil</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../recursos/css/style.css">
</head>
<body class="bg-gray-100 font-sans">
  <!-- Header -->
  <header class="bg-green-900 text-white shadow-lg">
    <div class="flex items-center justify-between px-6 py-4">
      <div class="flex items-center space-x-4">
        <!-- Botón del menú -->
        <button id="menuToggle" class="p-2 rounded-md hover:bg-green-800 transition">
          <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
          </svg>
        </button>
        <img src="../recursos/img/logoCD.png" alt="Logo CD Momil" class="w-12 h-12" />
        <h1 class="text-xl font-bold">Panel de Administración - C.D. Momil</h1>
      </div>
      <div class="flex items-center space-x-4 relative">
    <span class="text-base">Llegó <?php echo htmlspecialchars($admin_usuario); ?></span> 
    <div class="relative">
        <button id="profileToggle" class="w-10 h-10 bg-yellow-600 rounded-full flex items-center justify-center hover:bg-yellow-500 transition">
            <span class="text-sm font-bold">A</span>
        </button>
        <div id="profileDropdown" class="dropdown absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg border border-gray-200 z-50">
            <div class="py-2">
                <a href="#" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-100">
                    </a>
                <a href="../authe/logout.php" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-100">
                    <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 01-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"></path>
                    </svg>
                    Cerrar Sesión
                </a>
            </div>
        </div>
    </div>
</div>
    </div>
  </header>

  <!-- Overlay -->
  <div id="overlay" class="overlay fixed inset-0 bg-black bg-opacity-50 z-40"></div>

  <!-- Sidebar -->
  <nav id="sidebar" class="sidebar fixed left-0 top-0 h-full w-72 bg-green-800 shadow-lg z-50 pt-20">
    <div class="py-8">

      <!-- Dashboard -->
    <a href="../views/indexAdmin.php" class="flex items-center px-8 py-4 hover:bg-green-700 cursor-pointer group border-b border-green-700">
      <div class="w-8 h-8 bg-yellow-600 rounded-lg flex items-center justify-center mr-5">
        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
          <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
        </svg>
      </div>
      <span class="text-white font-semibold text-lg">Inicio</span>
    </a>

    <!-- Usuarios -->
    <a href="../views/indexUsuarios.php" class="flex items-center px-8 py-4 hover:bg-green-700 cursor-pointer group border-b border-green-700">
      <div class="w-8 h-8 bg-yellow-600 rounded-lg flex items-center justify-center mr-5">
        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
          <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
        </svg>
      </div>
      <span class="text-white font-semibold text-lg">Usuarios</span>
    </a>

    <!-- Entrenadores -->
    <a href="indexEntrenador.php" class="flex items-center px-8 py-4 hover:bg-green-700 cursor-pointer group border-b border-green-700">
      <div class="w-8 h-8 bg-yellow-600 rounded-lg flex items-center justify-center mr-5">
        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
          <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
        </svg>
      </div>
      <span class="text-white font-semibold text-lg">Entrenadores</span>
    </a>

    <!-- Jugadores -->
    <a href="indexJugador.php" class="flex items-center px-8 py-4 hover:bg-green-700 cursor-pointer group border-b border-green-700">
      <div class="w-8 h-8 bg-yellow-600 rounded-lg flex items-center justify-center mr-5">
        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"></path>
        </svg>
      </div>
      <span class="text-white font-semibold text-lg">Jugadores</span>
    </a>

    <!-- Partidos -->
    <a href="indexPartidos.php" class="flex items-center px-8 py-4 hover:bg-green-700 cursor-pointer group border-b border-green-700">
      <div class="w-8 h-8 bg-yellow-600 rounded-lg flex items-center justify-center mr-5">
        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path>
        </svg>
      </div>
      <span class="text-white font-semibold text-lg">Partidos</span>
    </a>

    <!-- Estadísticas -->
    <a href="indexEstadisticas.php" class="flex items-center px-8 py-4 hover:bg-green-700 cursor-pointer group border-b border-green-700">
      <div class="w-8 h-8 bg-yellow-600 rounded-lg flex items-center justify-center mr-5">
        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
          <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"></path>
          <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"></path>
        </svg>
      </div>
      <span class="text-white font-semibold text-lg">Plantilla</span>
    </a>

    

  </div>
</nav>

  <!-- Main Content -->
  <main class="p-8">
    <div class="bg-white rounded-lg shadow-md">
      <!-- Header de la sección -->
      <div class="px-8 py-6 border-b border-gray-200">
        <div class="flex justify-between items-center">
          <div>
            <h2 class="text-3xl font-bold text-gray-900">Gestión de Jugadores</h2>
            <p class="text-gray-600 mt-2">Administra los jugadores del club deportivo C.D. Momil</p>
          </div>
          <div class="flex space-x-3">
            <button id="btnNuevoJugador" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold transition flex items-center">
              <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
              </svg>
              Nuevo Jugador
            </button>
          </div>
        </div>
      </div>

      <!-- Filtros y búsqueda -->
<div class="px-8 py-4 bg-gray-50 border-b border-gray-200">
  <div class="flex justify-between items-center">
    <div class="flex space-x-4">
      <div class="relative">
        <input type="text" id="searchInput" placeholder="Buscar jugador..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent w-80">
        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
        </svg>
      </div>
      <select id="filterPosicion" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
        <option value="">Todas las posiciones</option>
        <option value="Portero">Portero</option>
        <option value="Defensa Central">Defensa Central</option>
        <option value="Lateral Derecho">Lateral Derecho</option>
        <option value="Lateral Izquierdo">Lateral Izquierdo</option>
        <option value="Mediocentro Defensivo">Mediocentro Defensivo</option>
        <option value="Mediocentro">Mediocentro</option>
        <option value="Mediocentro Ofensivo">Mediocentro Ofensivo</option>
        <option value="Extremo Derecho">Extremo Derecho</option>
        <option value="Extremo Izquierdo">Extremo Izquierdo</option>
        <option value="Delantero Centro">Delantero Centro</option>
      </select>
      <select id="filterGenero" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
        <option value="">Todos los géneros</option>
        <option value="Masculino">Masculino</option>
        <option value="Femenino">Femenino</option>
      </select>
      <!-- NUEVO FILTRO POR CATEGORÍA -->
      <select id="filterCategoria" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
        <option value="">Todas las categorías</option>
        <!-- Las categorías se cargarán dinámicamente -->
      </select>
    </div>
    <div class="text-sm text-gray-500">
      Total: <span id="totalJugadores" class="font-semibold">0</span> jugadores
    </div>
  </div>
</div>
      <!-- Tabla de jugadores -->
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jugador</th>
              <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Edad</th>
              <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Posición</th>
              <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dorsal</th>
              <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Físico</th>
              <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
              <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Género</th>
              <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
          </thead>
          <tbody id="tablaJugadores" class="bg-white divide-y divide-gray-200">
            <!-- Los datos se cargarán aquí dinámicamente -->
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <!-- Modal para Nuevo/Editar Jugador -->
  <div id="modalJugador" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl mx-4 max-h-screen overflow-y-auto">
      <div class="px-6 py-4 border-b border-gray-200">
        <h3 id="modalTitle" class="text-xl font-bold text-gray-900">Nuevo Jugador</h3>
      </div>
      <form id="formJugador" class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Información Personal -->
          <div class="md:col-span-3">
            <h4 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Información Personal</h4>
          </div>
          
          <div>
            <label for="cedula" class="block text-sm font-medium text-gray-700 mb-2">Cédula *</label>
            <input type="text" id="cedula" name="cedula" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
          </div>
          <div>
            <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">Nombre *</label>
            <input type="text" id="nombre" name="nombre" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
          </div>
          <div>
            <label for="apellido" class="block text-sm font-medium text-gray-700 mb-2">Apellido *</label>
            <input type="text" id="apellido" name="apellido" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
          </div>
          
          <div>
            <label for="fecha_nacimiento" class="block text-sm font-medium text-gray-700 mb-2">Fecha de Nacimiento *</label>
            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
          </div>
          <div>
            <label for="telefono" class="block text-sm font-medium text-gray-700 mb-2">Teléfono</label>
            <input type="tel" id="telefono" name="telefono" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
          </div>
          <div>
            <label for="genero" class="block text-sm font-medium text-gray-700 mb-2">Género *</label>
            <select id="genero" name="genero" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
              <option value="">Seleccionar género...</option>
              <option value="Masculino">Masculino</option>
              <option value="Femenino">Femenino</option>
            </select>
          </div>

          <!-- Información Física -->
          <div class="md:col-span-3 mt-6">
            <h4 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Información Física</h4>
          </div>
          
          <div>
            <label for="altura" class="block text-sm font-medium text-gray-700 mb-2">Altura (m)</label>
            <input type="number" id="altura" name="altura" step="0.01" min="0" max="3" placeholder="1.75" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
          </div>
          <div>
            <label for="peso" class="block text-sm font-medium text-gray-700 mb-2">Peso (kg)</label>
            <input type="number" id="peso" name="peso" step="0.1" min="0" max="200" placeholder="70.5" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
          </div>
          <div>
            <label for="posicion" class="block text-sm font-medium text-gray-700 mb-2">Posición</label>
            <select id="posicion" name="posicion" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
              <option value="">Seleccionar posición...</option>
              <option value="Portero">Portero</option>
              <option value="Defensa Central">Defensa Central</option>
              <option value="Lateral Derecho">Lateral Derecho</option>
              <option value="Lateral Izquierdo">Lateral Izquierdo</option>
              <option value="Mediocentro Defensivo">Mediocentro Defensivo</option>
              <option value="Mediocentro">Mediocentro</option>
              <option value="Mediocentro Ofensivo">Mediocentro Ofensivo</option>
              <option value="Extremo Derecho">Extremo Derecho</option>
              <option value="Extremo Izquierdo">Extremo Izquierdo</option>
              <option value="Delantero Centro">Delantero Centro</option>
            </select>
          </div>

          <!-- Información del Club -->
          <div class="md:col-span-3 mt-6">
            <h4 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Información del Club</h4>
          </div>
          
          <div>
            <label for="dorsal" class="block text-sm font-medium text-gray-700 mb-2">Número de Dorsal</label>
            <input type="number" id="dorsal" name="dorsal" min="1" max="99" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
          </div>
          <div>
            <label for="fecha_ingreso" class="block text-sm font-medium text-gray-700 mb-2">Fecha de Ingreso</label>
            <input type="date" id="fecha_ingreso" name="fecha_ingreso" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
          </div>
          <div>
            <label for="id_categoria" class="block text-sm font-medium text-gray-700 mb-2">Categoría</label>
            <select id="id_categoria" name="id_categoria" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
              <option value="">Seleccionar categoría...</option>
              <!-- Las categorías se cargarán dinámicamente -->
            </select>
          </div>
        </div>
        
        <div class="flex justify-end space-x-3 mt-6 pt-6 border-t border-gray-200">
          <button type="button" id="btnCancelar" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition">
            Cancelar
          </button>
          <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
            Guardar
          </button>
        </div>
      </form>
    </div>
  </div>

  <script src="../recursos/js/jugadorFrm.js"></script>
</body>
</html>