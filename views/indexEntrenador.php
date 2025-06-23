<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Entrenadores - C.D. Momil</title>
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
        <span class="text-base">Llegó el Admin</span>
        <div class="relative">
          <button id="profileToggle" class="w-10 h-10 bg-yellow-600 rounded-full flex items-center justify-center hover:bg-yellow-500 transition">
            <span class="text-sm font-bold">A</span>
          </button>
          <!-- Dropdown del perfil -->
          <div id="profileDropdown" class="dropdown absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg border border-gray-200 z-50">
            <div class="py-2">
              <a href="#" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-100">
                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"></path>
                </svg>
                Ver Perfil
              </a>
              <a href="#" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-100">
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
            <h2 class="text-3xl font-bold text-gray-900">Gestión de Entrenadores</h2>
            <p class="text-gray-600 mt-2">Administra los entrenadores del club deportivo C.D. Momil</p>
          </div>
          <div class="flex space-x-3">
            <button id="btnNuevoEntrenador" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold transition flex items-center">
              <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
              </svg>
              Nuevo Entrenador
            </button>
          </div>
        </div>
      </div>

      <!-- Filtros y búsqueda -->
      <div class="px-8 py-4 bg-gray-50 border-b border-gray-200">
        <div class="flex justify-between items-center">
          <div class="flex space-x-4">
            <div class="relative">
              <input type="text" id="searchInput" placeholder="Buscar entrenador..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent w-80">
              <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
              </svg>
            </div>
            <select id="filterEspecialidad" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
  <option value="">Todas las especialidades</option>
  <option value="Fútbol Profesional">Fútbol Profesional</option>
  <option value="Fútbol Semi Profesional">Fútbol Semi Profesional</option>
  <option value="Fútbol Amateur">Fútbol Amateur</option>

            </select>
          </div>
          <div class="text-sm text-gray-500">
            Total: <span id="totalEntrenadores" class="font-semibold">0</span> entrenadores
          </div>
        </div>
      </div>


<!-- Tabla de entrenadores -->
<div class="overflow-x-auto">
  <table class="w-full">
    <thead class="bg-gray-50">
      <tr>
        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entrenador</th>
        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contacto</th>
        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Especialidad</th>
        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Experiencia</th>
        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Contratación</th>
        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
      </tr>
    </thead>
    <tbody id="tablaEntrenadores" class="bg-white divide-y divide-gray-200">
      <!-- Los datos se cargarán aquí dinámicamente -->
    </tbody>
  </table>
</div>


  <!-- Modal para Nuevo/Editar Entrenador -->
  <div id="modalEntrenador" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 max-h-screen overflow-y-auto">
      <div class="px-6 py-4 border-b border-gray-200">
        <h3 id="modalTitle" class="text-xl font-bold text-gray-900">Nuevo Entrenador</h3>
      </div>
      <form id="formEntrenador" class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
            <label for="telefono" class="block text-sm font-medium text-gray-700 mb-2">Teléfono</label>
            <input type="tel" id="telefono" name="telefono" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
          </div>
          <div class="md:col-span-2">
            <label for="correo" class="block text-sm font-medium text-gray-700 mb-2">Correo Electrónico</label>
            <input type="email" id="correo" name="correo" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
          </div>
          <div>
            <label for="especialidad" class="block text-sm font-medium text-gray-700 mb-2">Especialidad</label>
            <select id="especialidad" name="especialidad" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
  <option value="">Seleccionar especialidad...</option>
  <option value="Fútbol Profesional">Fútbol Profesional</option>
  <option value="Fútbol Semi Profesional">Fútbol Semi Profesional</option>
  <option value="Fútbol Amateur">Fútbol Amateur</option>
</select>
          </div>
          <div>
            <label for="experiencia_años" class="block text-sm font-medium text-gray-700 mb-2">Años de Experiencia</label>
            <input type="number" id="experiencia_años" name="experiencia_años" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
          </div>
          <div class="md:col-span-2">
            <label for="fecha_contratacion" class="block text-sm font-medium text-gray-700 mb-2">Fecha de Contratación</label>
            <input type="date" id="fecha_contratacion" name="fecha_contratacion" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
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

  <!-- Modal para Asignar Categoría -->
  <div id="modalCategoria" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
      <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-xl font-bold text-gray-900">Asignar Categoría</h3>
      </div>
      <form id="formCategoria" class="p-6">
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Entrenador</label>
          <p id="entrenadorSeleccionado" class="text-gray-900 font-semibold"></p>
        </div>
        <div class="mb-6">
          <label for="categoria" class="block text-sm font-medium text-gray-700 mb-2">Categoría *</label>
          <select id="categoria" name="categoria" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
            <option value="">Seleccionar categoría</option>
            <option value="Sub-8">Sub-8</option>
            <option value="Sub-10">Sub-10</option>
            <option value="Sub-12">Sub-12</option>
            <option value="Sub-14">Sub-14</option>
            <option value="Sub-16">Sub-16</option>
            <option value="Sub-18">Sub-18</option>
            <option value="Sub-18">Sub-20</option>
           
          </select>
        </div>
        <div class="flex justify-end space-x-3">
          <button type="button" id="btnCancelarCategoria" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition">
            Cancelar
          </button>
          <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
            Asignar
          </button>
        </div>
      </form>
    </div>
  </div>
  <script src="../recursos/js/entrenadorFrm.js"></script>
  </body>
</html>