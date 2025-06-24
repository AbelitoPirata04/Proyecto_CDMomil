<?php
session_start(); // Iniciar sesión en cada página protegida

// Verificar si el administrador no está logueado
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Si no está logueado, redirigir a la página de login
    header('Location: index.php'); // Asume que index.php es tu página de login en la misma carpeta views/
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
  <title>Partidos - C.D. Momil</title>
  <script src="https://cdn.tailwindcss.com"></script>
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
        <span class="text-base">Llegó <?php echo htmlspecialchars($admin_usuario); ?></span> 
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

      <a href="indexPartidos.php" class="flex items-center px-8 py-4 hover:bg-green-700 cursor-pointer group border-b border-green-700 bg-green-700">
        <div class="w-8 h-8 bg-yellow-600 rounded-lg flex items-center justify-center mr-5">
          <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path>
          </svg>
        </div>
        <span class="text-white font-semibold text-lg">Partidos</span>
      </a>

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

  <main class="p-8">
    <div class="bg-white rounded-lg shadow-md">
      <div class="px-8 py-6 border-b border-gray-200">
        <div class="flex justify-between items-center">
          <div>
            <h2 class="text-3xl font-bold text-gray-900">Gestión de Partidos</h2>
            <p class="text-gray-600 mt-2">Administra los partidos del club deportivo C.D. Momil</p>
          </div>
          <div class="flex space-x-3">
            <button id="btnNuevoPartido" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold transition flex items-center">
              <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
              </svg>
              Nuevo Partido
            </button>
            <button id="btnDescargarInforme" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition flex items-center">
              <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L10 11.586l2.293-2.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
              </svg>
              Descargar Informe
            </button>
          </div>
        </div>
      </div>

      <div class="px-8 py-4 bg-gray-50 border-b border-gray-200">
        <div class="flex justify-between items-center">
          <div class="flex space-x-4">
            <select id="filterCategoria" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
              <option value="">Todas las categorías</option>
              </select>
          </div>
          <div class="text-sm text-gray-500">
            Total: <span id="totalPartidos" class="font-semibold">0</span> partidos
          </div>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha y Hora</th>
              <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rival</th>
              <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Localía</th>
              <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resultado</th>
              <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
              <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
          </thead>
          <tbody id="tablaPartidos" class="bg-white divide-y divide-gray-200">
            </tbody>
        </table>
      </div>
    </div> </main>

  <div id="modalPartido" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 max-h-screen overflow-y-auto">
      <div class="px-6 py-4 border-b border-gray-200">
        <h3 id="modalTitle" class="text-xl font-bold text-gray-900">Nuevo Partido</h3>
      </div>
      
      <form id="formPartido" class="p-6">
        <input type="hidden" id="partidoId" name="id_partido"> 
        
        <div id="paso1InfoPartido">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6"> 
              <div class="md:col-span-2">
                  <h4 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Información del Partido</h4>
              </div>
              
              <div>
                  <label for="fecha" class="block text-sm font-medium text-gray-700 mb-2">Fecha *</label>
                  <input type="date" id="fecha" name="fecha" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
              </div>
              
              <div>
                  <label for="hora" class="block text-sm font-medium text-gray-700 mb-2">Hora *</label>
                  <input type="time" id="hora" name="hora" required step="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
              </div>
              
              <div>
                  <label for="rival" class="block text-sm font-medium text-gray-700 mb-2">Equipo Rival *</label>
                  <input type="text" id="rival" name="rival" required placeholder="Nombre del equipo rival" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
              </div>
              
              <div>
                  <label for="localia" class="block text-sm font-medium text-gray-700 mb-2">Localía *</label>
                  <select id="localia" name="localia" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                      <option value="">Seleccionar...</option>
                      <option value="Local">Local</option>
                      <option value="Visitante">Visitante</option>
                  </select>
              </div>
              
              <div>
                  <label for="id_categoria" class="block text-sm font-medium text-gray-700 mb-2">Categoría *</label>
                  <select id="id_categoria" name="id_categoria" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                      <option value="">Seleccionar categoría...</option>
                  </select>
              </div>

              <div class="md:col-span-2 mt-6">
                  <h4 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Resultado del Partido</h4>
              </div>
              
              <div>
                  <label for="goles_favor" class="block text-sm font-medium text-gray-700 mb-2">Goles a Favor</label>
                  <input type="number" id="goles_favor" name="goles_favor" min="0" placeholder="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
              </div>
              
              <div>
                  <label for="goles_contra" class="block text-sm font-medium text-gray-700 mb-2">Goles en Contra</label>
                  <input type="number" id="goles_contra" name="goles_contra" min="0" placeholder="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
              </div>
          </div>
        </div> <div id="paso2EstadisticasJugadores" class="hidden">
            <div class="md:col-span-2 mt-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Estadísticas de Jugadores</h4>
                <p class="text-sm text-gray-600 mb-4">Selecciona los jugadores que participaron y registra sus estadísticas para este partido.</p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jugó</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jugador (Dorsal / Posición)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Minutos</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Goles</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">TA</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">TR</th>
                        </tr>
                    </thead>
                    <tbody id="tablaEstadisticasJugadores" class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">Cargando jugadores...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div> <div class="flex justify-between space-x-3 mt-6 pt-6 border-t border-gray-200">
          <button type="button" id="btnCancelar" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition">
            Cancelar
          </button>
          <button type="submit" id="btnGuardarPaso1" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
            Guardar Partido y Continuar
          </button>
          <button type="button" id="btnAtrasPaso2" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition hidden">
            Atrás
          </button>
          <button type="button" id="btnGuardarPaso2" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition hidden">
            Guardar Estadísticas
          </button>
        </div>
      </form>
    </div>
  </div> <div id="modalDetallePartido" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl mx-4 max-h-screen overflow-y-auto">
          <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
              <h3 class="text-xl font-bold text-gray-900">Detalles del Partido</h3>
              <button id="btnCloseDetalleModal" class="text-gray-500 hover:text-gray-700">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
              </button>
          </div>
          
          <div class="p-6">
              <div class="mb-6 pb-4 border-b border-gray-200">
                  <h4 class="text-lg font-semibold text-gray-900 mb-4">Información del Partido</h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-700">
                      <div>
                          <p class="font-medium">Fecha y Hora:</p>
                          <p id="detalleFechaHora" class="text-gray-900"></p>
                      </div>
                      <div>
                          <p class="font-medium">Equipo Rival:</p>
                          <p id="detalleRival" class="text-gray-900"></p>
                      </div>
                      <div>
                          <p class="font-medium">Localía:</p>
                          <p id="detalleLocalia" class="text-gray-900"></p>
                      </div>
                      <div>
                          <p class="font-medium">Resultado:</p>
                          <p id="detalleResultado" class="text-gray-900"></p>
                      </div>
                      <div>
                          <p class="font-medium">Categoría:</p>
                          <p id="detalleCategoria" class="text-gray-900"></p>
                      </div>
                  </div>
              </div>

              <div>
                  <h4 class="text-lg font-semibold text-gray-900 mb-4">Estadísticas de Jugadores</h4>
                  <div class="overflow-x-auto border border-gray-200 rounded-lg">
                      <table class="min-w-full divide-y divide-gray-200">
                          <thead class="bg-gray-50">
                              <tr>
                                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jugador (Dorsal / Posición)</th>
                                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Minutos Jugados</th>
                                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Goles</th>
                                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">TA</th>
                                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">TR</th>
                              </tr>
                          </thead>
                          <tbody id="tablaDetalleJugadoresStats" class="bg-white divide-y divide-gray-200">
                              <tr>
                                  <td colspan="5" class="px-6 py-4 text-center text-gray-500">Cargando estadísticas...</td>
                              </tr>
                          </tbody>
                      </table>
                  </div>
              </div>
          </div>
      </div>
  </div> <div id="modalInformeCategoria" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-sm mx-4">
      <div class="flex justify-between items-center border-b pb-3 mb-4">
        <h3 class="text-lg font-bold text-gray-900">Seleccionar Categoría para Informe</h3>
        <button id="btnCloseInformeCategoriaModal" class="text-gray-500 hover:text-gray-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>
      
      <div class="mb-4">
        <label for="selectCategoriaInforme" class="block text-sm font-medium text-gray-700 mb-2">Categoría:</label>
        <select id="selectCategoriaInforme" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
          <option value="">Todas las categorías</option>
          </select>
      </div>
      
      <div class="flex justify-end space-x-3">
        <button type="button" id="btnCancelarInformeCategoria" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition">
          Cancelar
        </button>
        <button type="button" id="btnGenerarInforme" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
          Generar Informe
        </button>
      </div>
    </div>
  </div>

  <script src="../recursos/js/partidoFrm.js"></script>
 </body>
</html>