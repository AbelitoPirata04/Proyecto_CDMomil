<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Plantilla - C.D. Momil</title> <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../recursos/css/style.css">
</head>
<body class="bg-gray-100 font-sans">
  <header class="bg-green-900 text-white shadow-lg">
    <div class="flex items-center justify-between px-6 py-4">
      <div class="flex items-center space-x-4">
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

  <div id="overlay" class="overlay fixed inset-0 bg-black bg-opacity-50 z-40"></div>

  <nav id="sidebar" class="sidebar fixed left-0 top-0 h-full w-72 bg-green-800 shadow-lg z-50 pt-20">
    <div class="py-8">

      <a href="../views/indexAdmin.php" class="flex items-center px-8 py-4 hover:bg-green-700 cursor-pointer group border-b border-green-700">
      <div class="w-8 h-8 bg-yellow-600 rounded-lg flex items-center justify-center mr-5">
        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
          <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
        </svg>
      </div>
      <span class="text-white font-semibold text-lg">Inicio</span>
    </a>

    <a href="../views/indexUsuarios.php" class="flex items-center px-8 py-4 hover:bg-green-700 cursor-pointer group border-b border-green-700">
      <div class="w-8 h-8 bg-yellow-600 rounded-lg flex items-center justify-center mr-5">
        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
          <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
        </svg>
      </div>
      <span class="text-white font-semibold text-lg">Usuarios</span>
    </a>

    <a href="indexEntrenador.php" class="flex items-center px-8 py-4 hover:bg-green-700 cursor-pointer group border-b border-green-700">
      <div class="w-8 h-8 bg-yellow-600 rounded-lg flex items-center justify-center mr-5">
        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
          <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
        </svg>
      </div>
      <span class="text-white font-semibold text-lg">Entrenadores</span>
    </a>

    <a href="indexJugador.php" class="flex items-center px-8 py-4 hover:bg-green-700 cursor-pointer group border-b border-green-700">
      <div class="w-8 h-8 bg-yellow-600 rounded-lg flex items-center justify-center mr-5">
        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"></path>
        </svg>
      </div>
      <span class="text-white font-semibold text-lg">Jugadores</span>
    </a>

    <a href="indexPartidos.php" class="flex items-center px-8 py-4 hover:bg-green-700 cursor-pointer group border-b border-green-700">
      <div class="w-8 h-8 bg-yellow-600 rounded-lg flex items-center justify-center mr-5">
        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path>
        </svg>
      </div>
      <span class="text-white font-semibold text-lg">Partidos</span>
    </a>

    <a href="indexEstadisticas.php" class="flex items-center px-8 py-4 hover:bg-green-700 cursor-pointer group bg-green-700">
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

  <main class="p-8">
    <div class="bg-white rounded-lg shadow-md">
      <div class="px-8 py-6 border-b border-gray-200">
        <div class="flex justify-between items-center">
          <div>
            <h2 class="text-3xl font-bold text-gray-900">Estadísticas de la Plantilla</h2>
            <p class="text-gray-600 mt-2">Consulta las estadísticas acumuladas de todos los jugadores</p>
          </div>
          <div class="flex space-x-3">
            <button id="btnDescargarInforme" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition flex items-center">
              <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L10 11.586l2.293-2.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
              </svg>
              Descargar Informe
            </button>
          </div>
        </div>
      </div>

      <div class="px-8 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
        <div class="flex space-x-4">
          <select id="filterCategoria" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
            <option value="">Todas las categorías</option>
            </select>

          <select id="sortOrder" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
            <option value="">Ordenar por...</option>
            <option value="goles_totales_desc">Máximo Goleador (Goles)</option>
            <option value="partidos_jugados_desc">Más Partidos Jugados</option>
            <option value="minutos_jugados_desc">Más Minutos Jugados</option>
            <option value="tarjetas_amarillas_desc">Más Tarjetas Amarillas</option>
            <option value="tarjetas_rojas_desc">Más Tarjetas Rojas</option>
            <option value="nombre_asc">Nombre (A-Z)</option>
          </select>
        </div>
        <div class="text-sm text-gray-500">
          Total Jugadores: <span id="totalJugadoresStats" class="font-semibold">0</span>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jugador</th>
              <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
              <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Goles Totales</th>
              <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Partidos Jugados</th>
              <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Minutos Jugados</th>
              <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarjetas Amarillas</th>
              <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarjetas Rojas</th>
            </tr>
          </thead>
          <tbody id="tablaEstadisticasPlantilla" class="bg-white divide-y divide-gray-200">
            <tr>
              <td colspan="7" class="px-6 py-8 text-center text-gray-500">Cargando estadísticas...</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <script src="../recursos/js/estadisticasFrm.js"></script>
</body>
</html>