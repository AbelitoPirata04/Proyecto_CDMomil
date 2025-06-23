// ============================
// VARIABLES GLOBALES
// ============================
let allPartidos = []; // Para almacenar todos los partidos cargados
let allCategorias = []; // Para almacenar todas las categorías cargadas
let partidoEditandoId = null; // Para saber si estamos editando o creando un partido nuevo

// Referencias a elementos del layout
const menuToggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');
const profileToggle = document.getElementById('profileToggle');
const profileDropdown = document.getElementById('profileDropdown');

// Referencias a elementos de la vista de Partidos
const tablaPartidosBody = document.getElementById('tablaPartidos');
const totalPartidosSpan = document.getElementById('totalPartidos');
const filterCategoriaSelect = document.getElementById('filterCategoria'); // El único filtro
const btnNuevoPartido = document.getElementById('btnNuevoPartido');
const btnDescargarInforme = document.getElementById('btnDescargarInforme'); // Si existe en tu HTML

// Referencias al modal de Partido
const modalPartido = document.getElementById('modalPartido');
const formPartido = document.getElementById('formPartido');
const modalTitle = document.getElementById('modalTitle');
const btnCancelarModal = document.getElementById('btnCancelar'); 
const selectCategoriaModal = document.getElementById('id_categoria'); 
const partidoIdInput = document.getElementById('partidoId'); // Campo oculto para el ID del partido

// Referencias a elementos de los pasos del modal
const paso1InfoPartido = document.getElementById('paso1InfoPartido');
const paso2EstadisticasJugadores = document.getElementById('paso2EstadisticasJugadores');

// Referencias a los botones de navegación de los pasos
const btnGuardarPaso1 = document.getElementById('btnGuardarPaso1'); // El botón original 'Guardar Partido y Continuar'
const btnAtrasPaso2 = document.getElementById('btnAtrasPaso2');
const btnGuardarPaso2 = document.getElementById('btnGuardarPaso2');

// Referencia a la tabla de estadísticas de jugadores dentro del modal
const tablaEstadisticasJugadores = document.getElementById('tablaEstadisticasJugadores');


// ============================
// FUNCIONES DE LA INTERFAZ (MENÚ Y DROPDOWN)
// ============================

function toggleMenu() {
    // Usar 'open' para coincidir con tu CSS
    if (sidebar) sidebar.classList.toggle('open');
    if (overlay) overlay.classList.toggle('open');
}

function toggleProfile() {
    // Usar 'open' para coincidir con tu CSS para el dropdown
    if (profileDropdown) profileDropdown.classList.toggle('open');
}

// ============================
// EVENT LISTENERS DEL MENÚ Y DROPDOWN
// ============================
if (menuToggle) menuToggle.addEventListener('click', toggleMenu);
if (overlay) overlay.addEventListener('click', toggleMenu);
if (profileToggle) profileToggle.addEventListener('click', toggleProfile);

// Cerrar dropdown del perfil al hacer clic fuera
document.addEventListener('click', (e) => {
    // Usar 'open' para coincidir con tu CSS para el dropdown
    if (profileToggle && profileDropdown && !profileToggle.contains(e.target) && !profileDropdown.contains(e.target)) {
        profileDropdown.classList.remove('open');
    }
});

// Cerrar menú al hacer clic en una opción (asumiendo que las opciones de sidebar tienen la clase 'cursor-pointer')
if (sidebar && overlay) { // Asegurarse de que sidebar y overlay existan
    const menuItems = sidebar.querySelectorAll('.cursor-pointer');
    menuItems.forEach(item => {
        item.addEventListener('click', () => {
            sidebar.classList.remove('open'); // Usar 'open'
            overlay.classList.remove('open'); // Usar 'open'
        });
    });
}

// ============================
// FUNCIONES DE NOTIFICACIÓN
// ============================

function mostrarNotificacion(mensaje, tipo = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 transition-all transform ${
        tipo === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    }`;
    notification.textContent = mensaje;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('opacity-0', 'translate-x-full');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// ============================
// FUNCIONES PARA LA VISTA DE PARTIDOS
// ============================

// Función para obtener el nombre de la categoría por su ID
const getCategoryNameById = (id) => {
    const categoria = allCategorias.find(cat => cat.id_categoria == id);
    return categoria ? categoria.nombre : 'Desconocida';
};

/**
 * Renderiza los partidos en la tabla HTML.
 * @param {Array} partidosToRender - Array de objetos de partido a mostrar.
 */
function renderizarTablaPartidos(partidosToRender) {
    if (!tablaPartidosBody) return; // Asegurarse de que el elemento exista
    tablaPartidosBody.innerHTML = ''; // Limpiar la tabla
    
    if (partidosToRender.length === 0) {
        tablaPartidosBody.innerHTML = `  
            <tr>
                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                    <div class="flex flex-col items-center">
                        <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                        <p class="text-lg font-medium">No hay partidos registrados</p>
                        <p class="text-sm">Agrega el primer partido para comenzar</p>
                    </div>
                </td>
            </tr>
        `;
        if (totalPartidosSpan) totalPartidosSpan.textContent = 0;
        return;
    }

    partidosToRender.forEach(partido => {
        const categoriaNombre = getCategoryNameById(partido.id_categoria);
        // Desestructurar el resultado para obtener goles a favor y en contra
        const [golesFavor, golesContra] = partido.resultado ? partido.resultado.split('-').map(Number) : [null, null];
        
        let estadoPartido = 'Pendiente'; // Estado por defecto si no hay resultado o es null
        let resultadoColorClass = 'bg-blue-100 text-blue-800'; // Default para 'Pendiente'

        if (golesFavor !== null && golesContra !== null) {
            if (golesFavor > golesContra) {
                estadoPartido = 'Victoria';
                resultadoColorClass = 'bg-green-100 text-green-800';
            } else if (golesFavor < golesContra) {
                estadoPartido = 'Derrota';
                resultadoColorClass = 'bg-red-100 text-red-800';
            } else {
                estadoPartido = 'Empate';
                resultadoColorClass = 'bg-yellow-100 text-yellow-800';
            }
        }

        const golesFavorBgClass = (golesFavor !== null && golesFavor >= 0) ? 'bg-green-600' : 'bg-gray-400';
        const golesContraBgClass = (golesContra !== null && golesContra >= 0) ? 'bg-red-600' : 'bg-gray-400';

        const localiaIcon = partido.local_visitante === 'Local' ? 
            `<svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path></svg>` : 
            `<svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 6.707 6.293a1 1 0 00-1.414 1.414l4 4a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>`;

        const row = `
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <div class="text-sm font-medium text-gray-900">${partido.fecha}</div>
                            <div class="text-sm text-gray-500">${partido.hora ? partido.hora.substring(0, 5) : 'N/A'}</div> 
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-8 w-8">
                            <div class="h-8 w-8 rounded-full bg-gradient-to-r from-gray-400 to-gray-600 flex items-center justify-center">
                               <span class="text-xs font-bold text-white">${partido.rival ? partido.rival.charAt(0) : '-'}</span>
                            </div>
                        </div>
                        <div class="ml-3">
                            <div class="text-sm font-medium text-gray-900">${partido.rival}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full ${partido.local_visitante === 'Local' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'}">
                        ${localiaIcon}
                        ${partido.local_visitante}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center space-x-2">
                        <div class="flex items-center ${golesFavorBgClass} text-white px-3 py-1 rounded-lg">
                            <span class="font-bold text-lg">${golesFavor !== null ? golesFavor : '-'}</span>
                        </div>
                        <span class="text-gray-400">-</span>
                        <div class="flex items-center ${golesContraBgClass} text-white px-3 py-1 rounded-lg">
                            <span class="font-bold text-lg">${golesContra !== null ? golesContra : '-'}</span>
                        </div>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${resultadoColorClass}">
                            ${estadoPartido}
                        </span>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                        ${categoriaNombre}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex space-x-2">
                        <button onclick="window.verDetallePartido('${partido.id_partido}')" 
                                class="text-blue-600 hover:text-blue-900 transition-colors duration-200 px-3 py-1 rounded-md hover:bg-blue-50">
                            <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                            </svg>
                            Ver Detalle
                        </button>
                        <button onclick="window.editarPartido('${partido.id_partido}')" 
                                class="text-indigo-600 hover:text-indigo-900 transition-colors duration-200 px-3 py-1 rounded-md hover:bg-indigo-50">
                            <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                            </svg>
                            Editar
                        </button>
                        <button onclick="window.eliminarPartido('${partido.id_partido}', '${partido.rival}')" 
                                class="text-red-600 hover:text-red-900 transition-colors duration-200 px-3 py-1 rounded-md hover:bg-red-50">
                            <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"></path>
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 012 0v4a1 1 0 11-2 0V7zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V7a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            Eliminar
                        </button>
                    </div>
                </td>
            </tr>
        `;
        tablaPartidosBody.innerHTML += row;
    });
}

/**
 * Actualiza el contador de partidos mostrados.
 */
function actualizarContadorPartidos() {
    if (totalPartidosSpan) totalPartidosSpan.textContent = allPartidos.length;
}

/**
 * Abre el modal para crear un nuevo partido.
 */
function abrirModalNuevoPartido() {
    partidoEditandoId = null;
    modalTitle.textContent = 'Nuevo Partido';
    formPartido.reset(); // Limpiar el formulario
    if (partidoIdInput) partidoIdInput.value = ''; // Asegurarse de que el campo oculto de ID esté vacío
    if (modalPartido) modalPartido.classList.remove('hidden'); // Mostrar el modal
}

/**
 * Cierra el modal de partido.
 */
function cerrarModalPartido() {
    if (modalPartido) modalPartido.classList.add('hidden'); // Ocultar el modal
    if (formPartido) formPartido.reset(); // Resetear el formulario
    partidoEditandoId = null; // Limpiar el ID de partido en edición
}

/**
 * Controla la visibilidad de los pasos del modal y los botones de navegación.
 * @param {number} paso - El paso que se debe mostrar (1 o 2).
 */
function mostrarPasoModal(paso) {
    if (!paso1InfoPartido || !paso2EstadisticasJugadores || !btnGuardarPaso1 || !btnAtrasPaso2 || !btnGuardarPaso2 || !modalTitle) {
        console.error('Error: Elementos del modal no encontrados para controlar los pasos.');
        return;
    }

    if (paso === 1) {
        paso1InfoPartido.classList.remove('hidden');
        paso2EstadisticasJugadores.classList.add('hidden');

        btnGuardarPaso1.classList.remove('hidden');
        btnAtrasPaso2.classList.add('hidden');
        btnGuardarPaso2.classList.add('hidden');

        modalTitle.textContent = partidoEditandoId ? 'Editar Partido' : 'Nuevo Partido';

        // Es importante que el tipo del botón de Guardar Paso 1 sea submit
        // para la primera parte del flujo
        btnGuardarPaso1.type = 'submit'; 

    } else if (paso === 2) {
        paso1InfoPartido.classList.add('hidden');
        paso2EstadisticasJugadores.classList.remove('hidden');

        btnGuardarPaso1.classList.add('hidden');
        btnAtrasPaso2.classList.remove('hidden');
        btnGuardarPaso2.classList.remove('hidden');

        modalTitle.textContent = 'Registrar Estadísticas';

        // Cambiar el tipo del botón de Guardar Paso 1 a button
        // para que no vuelva a enviar el formulario base si se activa por accidente
        btnGuardarPaso1.type = 'button'; 
    }
}


/**
 * Obtiene las categorías desde la API y las carga en los selects.
 */
async function obtenerCategorias() {
    try {
        const response = await fetch('../api/categoriaAPI.php'); // Asegura esta ruta
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        const data = await response.json();
        allCategorias = data; // Almacenar categorías globalmente

        // Llenar el select de filtro de categorías
        if (filterCategoriaSelect) { // Asegurarse de que el elemento exista
            filterCategoriaSelect.innerHTML = '<option value="">Todas las categorías</option>';
        }
        // Llenar el select de categorías en el modal
        if (selectCategoriaModal) { // Asegurarse de que el elemento exista
            selectCategoriaModal.innerHTML = '<option value="">Seleccionar categoría...</option>';
        }

        allCategorias.forEach(categoria => {
            if (filterCategoriaSelect) {
                const optionFilter = document.createElement('option');
                optionFilter.value = categoria.id_categoria; // Usar ID para el filtro
                optionFilter.textContent = categoria.nombre;
                filterCategoriaSelect.appendChild(optionFilter);
            }

            if (selectCategoriaModal) {
                const optionModal = document.createElement('option');
                optionModal.value = categoria.id_categoria;
                optionModal.textContent = `${categoria.nombre} (${categoria.edad_minima}-${categoria.edad_maxima} años)`;
                selectCategoriaModal.appendChild(optionModal);
            }
        });
    } catch (error) {
        console.error('Error al cargar categorías:', error);
        mostrarNotificacion('Error al cargar las categorías.', 'error');
    }
}

/**
 * Obtiene los partidos desde la API y los renderiza en la tabla.
 * @param {string|null} id_categoria - ID de la categoría para filtrar, o null para todos.
 */
async function obtenerPartidos(id_categoria = null) {
    let url = '../api/partidosAPI.php'; // RUTA A LA API DE PARTIDOS
    if (id_categoria) {
        url += `?id_categoria=${id_categoria}`;
    }
    try {
        const response = await fetch(url);
        if (!response.ok) {
            let errorMsg = `Error HTTP: ${response.status}`;
            try {
                const errorData = await response.json();
                if (errorData && errorData.message) {
                    errorMsg += ` - ${errorData.message}`;
                }
            } catch (jsonError) {
                errorMsg += ` - No se pudo parsear la respuesta de error como JSON.`;
            }
            throw new Error(errorMsg);
        }
        const data = await response.json();
        allPartidos = data; 
        renderizarTablaPartidos(allPartidos); 
        actualizarContadorPartidos();
    } catch (error) {
        console.error('Error al cargar partidos:', error);
        mostrarNotificacion(`Error al cargar los partidos: ${error.message || error}.`, 'error');
    }
}

/**
 * Carga los jugadores de una categoría específica y los renderiza
 * en la tabla de estadísticas del Paso 2 del modal.
 * @param {string} idPartido - El ID del partido recién creado/editado.
 * @param {string} idCategoria - El ID de la categoría del partido.
 */
async function cargarJugadoresParaEstadisticas(idPartido, idCategoria) {
    if (!tablaEstadisticasJugadores) return; // Asegurar que el elemento exista

    tablaEstadisticasJugadores.innerHTML = `
        <tr>
            <td colspan="6" class="px-6 py-4 text-center text-gray-500">Cargando jugadores...</td>
        </tr>
    `;
    try {
        const response = await fetch(`../api/jugadorAPI.php?id_categoria=${idCategoria}`); // API para jugadores por categoría
        if (!response.ok) throw new Error('Error al cargar jugadores de la categoría.');
        const jugadoresCategoria = await response.json();

        tablaEstadisticasJugadores.innerHTML = ''; // Limpiar después de cargar
        if (jugadoresCategoria.length === 0) {
            tablaEstadisticasJugadores.innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">No hay jugadores en esta categoría.</td>
                </tr>
            `;
            return;
        }

        jugadoresCategoria.forEach(jugador => {
            const row = `
                <tr data-jugador-id="${jugador.id_jugador}">
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <input type="checkbox" name="jugo_${jugador.id_jugador}" class="form-checkbox h-5 w-5 text-green-600">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        ${jugador.nombre} ${jugador.apellido} (${jugador.dorsal ? 'Dorsal: ' + jugador.dorsal : ''} - ${jugador.posicion || 'Sin posición'})
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <input type="number" name="minutos_${jugador.id_jugador}" min="0" max="120" value="0" class="w-20 px-2 py-1 border rounded text-sm">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <input type="number" name="goles_${jugador.id_jugador}" min="0" value="0" class="w-16 px-2 py-1 border rounded text-sm">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <input type="number" name="ta_${jugador.id_jugador}" min="0" value="0" class="w-16 px-2 py-1 border rounded text-sm">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <input type="number" name="tr_${jugador.id_jugador}" min="0" max="1" value="0" class="w-16 px-2 py-1 border rounded text-sm">
                    </td>
                </tr>
            `;
            tablaEstadisticasJugadores.innerHTML += row;
        });

        // NOTA: Para edición, precargar estadísticas de jugador_partido sería más complejo
        // Implicaría otra API para obtener las estadísticas existentes para este partido y jugador,
        // y luego llenar los campos y marcar los checkboxes. Lo podemos abordar si lo necesitas.

    } catch (error) {
        console.error('Error al cargar jugadores para estadísticas:', error);
        if (tablaEstadisticasJugadores) {
            tablaEstadisticasJugadores.innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-red-500">Error al cargar jugadores: ${error.message}</td>
                </tr>
            `;
        }
        mostrarNotificacion('Error al cargar jugadores para estadísticas.', 'error');
    }
}

/**
 * Maneja el envío del formulario de partido (crear o actualizar).
 * MODIFICADO PARA MANEJAR LOS PASOS.
 */
async function handleFormPartidoSubmit(event) {
    event.preventDefault(); // Siempre prevenir el comportamiento por defecto

    // Si estamos en el Paso 1 (Información del Partido)
    // Se usa 'paso1InfoPartido' porque 'handleFormPartidoSubmit' es el listener del FORM
    // y siempre se dispara sin importar qué botón submit se presione.
    // Verificamos si el Paso 1 está visible para saber qué acción ejecutar.
    if (paso1InfoPartido && !paso1InfoPartido.classList.contains('hidden')) { 
        const formData = new FormData(formPartido);
        const partidoData = Object.fromEntries(formData.entries());

        partidoData.goles_favor = partidoData.goles_favor === '' ? 0 : parseInt(partidoData.goles_favor);
        partidoData.goles_contra = partidoData.goles_contra === '' ? 0 : parseInt(partidoData.goles_contra);
        partidoData.resultado = `${partidoData.goles_favor}-${partidoData.goles_contra}`;

        partidoData.local_visitante = partidoData.localia;
        delete partidoData.localia;

        const method = partidoEditandoId ? 'PUT' : 'POST';
        const url = partidoEditandoId 
            ? `../api/partidosAPI.php?id_partido=${partidoEditandoId}` 
            : '../api/partidosAPI.php'; 

        try {
            const response = await fetch(url, {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(partidoData)
            });

            const result = await response.json();

            if (result.success) {
                mostrarNotificacion(partidoEditandoId ? 'Partido actualizado exitosamente' : 'Partido registrado exitosamente');
                
                // Si es un nuevo partido, guardamos el ID para el paso 2
                if (!partidoEditandoId && result.id_partido) {
                    if (partidoIdInput) partidoIdInput.value = result.id_partido; 
                    partidoEditandoId = result.id_partido; 
                }
                
                // Pasamos al Paso 2: Cargar y mostrar jugadores para estadísticas
                const categoriaId = partidoData.id_categoria;
                await cargarJugadoresParaEstadisticas(partidoEditandoId, categoriaId);
                mostrarPasoModal(2); // Mostrar el Paso 2

                // Recargar la tabla principal de partidos en segundo plano
                obtenerPartidos(filterCategoriaSelect.value === '' ? null : filterCategoriaSelect.value); 

            } else {
                mostrarNotificacion(result.message || 'Error al guardar el partido.', 'error');
                console.error('Error al guardar partido:', result.message);
            }
        } catch (error) {
            mostrarNotificacion('Error en la comunicación con el servidor al guardar el partido.', 'error');
            console.error('Error en la petición API al guardar partido:', error);
        }
    } else {
        // Esto se ejecuta si el formulario se envía desde el Paso 2, pero
        // el botón "Guardar Estadísticas" es de tipo "button" y tiene su propio listener,
        // por lo que esta rama solo debería ser un fallback o no ejecutarse.
        console.log("handleFormPartidoSubmit llamado desde Paso 2. Usar btnGuardarPaso2.");
    }
}

/**
 * Muestra los detalles de un partido en un alert.
 * @param {string} id - ID del partido a ver.
 */
window.verDetallePartido = (id) => {
    const partido = allPartidos.find(p => p.id_partido == id);
    if (partido) {
        const categoriaNombre = getCategoryNameById(partido.id_categoria);
        alert(`Detalles del Partido:\nID: ${partido.id_partido}\nFecha: ${partido.fecha}\nHora: ${partido.hora ? partido.hora.substring(0, 5) : 'N/A'}\nRival: ${partido.rival}\nLocalía: ${partido.local_visitante}\nResultado: ${partido.resultado}\nCategoría: ${categoriaNombre}`);
    } else {
        mostrarNotificacion('Partido no encontrado para ver detalles.', 'error');
    }
};

/**
 * Abre el modal para editar un partido existente y precarga sus datos.
 * @param {string} id - ID del partido a editar.
 */
window.editarPartido = (id) => {
    const partido = allPartidos.find(p => p.id_partido == id);
    if (!partido) {
        mostrarNotificacion('Partido no encontrado para editar.', 'error');
        return;
    }
    
    partidoEditandoId = id; 
    modalTitle.textContent = 'Editar Partido';
    
    if (partidoIdInput) partidoIdInput.value = partido.id_partido; 
    document.getElementById('fecha').value = partido.fecha;
    document.getElementById('hora').value = partido.hora || ''; // Pasar la hora completa (HH:MM:SS) o vacío si es null
    document.getElementById('rival').value = partido.rival;
    if (document.getElementById('localia')) document.getElementById('localia').value = partido.local_visitante; 
    
    const [golesFavor, golesContra] = partido.resultado ? partido.resultado.split('-').map(Number) : ['', ''];
    if (document.getElementById('goles_favor')) document.getElementById('goles_favor').value = golesFavor;
    if (document.getElementById('goles_contra')) document.getElementById('goles_contra').value = golesContra;
    
    if (document.getElementById('id_categoria')) document.getElementById('id_categoria').value = partido.id_categoria;
    
    // Al editar, siempre mostrar el Paso 1 al inicio
    mostrarPasoModal(1); 
    if (modalPartido) modalPartido.classList.remove('hidden'); 
};

/**
 * Elimina un partido de la base de datos.
 * @param {string} id - ID del partido a eliminar.
 * @param {string} rival - Nombre del rival para la confirmación.
 */
window.eliminarPartido = async (id, rival) => {
    if (confirm(`¿Estás seguro de que quieres eliminar el partido contra ${rival}? Esta acción es irreversible y eliminará también las estadísticas de los jugadores asociadas.`)) {
        try {
            const response = await fetch(`../api/partidosAPI.php?id_partido=${id}`, { 
                method: 'DELETE'
            });
            const result = await response.json();

            if (result.success) {
                mostrarNotificacion(result.message);
                obtenerPartidos(filterCategoriaSelect.value === '' ? null : filterCategoriaSelect.value); 
            } else {
                mostrarNotificacion(result.message || 'Error al eliminar partido.', 'error');
                console.error('Error al eliminar partido:', result.message);
            }
        } catch (error) {
            mostrarNotificacion('Error en la comunicación con el servidor al eliminar el partido.', 'error');
            console.error('Error en la petición API DELETE:', error);
        }
    }
};

// ============================
// EVENT LISTENERS DE LA VISTA PARTIDOS
// ============================

// Botones de acción del panel
if (btnNuevoPartido) btnNuevoPartido.addEventListener('click', () => {
    abrirModalNuevoPartido();
    mostrarPasoModal(1); // Siempre empezar en el Paso 1 al abrir el modal
});

// Botones del modal (btnCancelarModal se mantiene igual, cierra el modal)
if (btnCancelarModal) btnCancelarModal.addEventListener('click', () => {
    cerrarModalPartido();
    mostrarPasoModal(1); // Resetear a Paso 1 al cerrar
});

// Botón "Atrás" en el Paso 2
if (btnAtrasPaso2) btnAtrasPaso2.addEventListener('click', () => {
    mostrarPasoModal(1);
});

// Botón "Guardar Estadísticas" en el Paso 2
if (btnGuardarPaso2) btnGuardarPaso2.addEventListener('click', async () => {
    const idPartido = partidoIdInput.value;
    if (!idPartido) {
        mostrarNotificacion('Error: ID de partido no encontrado. No se pueden guardar estadísticas.', 'error');
        return;
    }

    const jugadoresStats = [];
    if (tablaEstadisticasJugadores) { // Asegurarse de que la tabla de stats exista
        const filasJugadores = tablaEstadisticasJugadores.querySelectorAll('tr[data-jugador-id]');

        filasJugadores.forEach(fila => {
            const jugadorId = fila.dataset.jugadorId;
            const jugoCheckbox = fila.querySelector(`input[name="jugo_${jugadorId}"]`);
            
            // Solo procesar si el jugador "jugó" (checkbox marcado)
            if (jugoCheckbox && jugoCheckbox.checked) {
                const minutosInput = fila.querySelector(`input[name="minutos_${jugadorId}"]`);
                const golesInput = fila.querySelector(`input[name="goles_${jugadorId}"]`);
                const taInput = fila.querySelector(`input[name="ta_${jugadorId}"]`);
                const trInput = fila.querySelector(`input[name="tr_${jugadorId}"]`);

                jugadoresStats.push({
                    id_jugador: jugadorId,
                    id_partido: idPartido,
                    minutos_jugados: parseInt(minutosInput ? minutosInput.value : 0) || 0,
                    goles: parseInt(golesInput ? golesInput.value : 0) || 0,
                    tarjetas_amarillas: parseInt(taInput ? taInput.value : 0) || 0,
                    tarjetas_rojas: parseInt(trInput ? trInput.value : 0) || 0
                });
            }
        });
    }

    console.log("Datos de estadísticas a enviar:", jugadoresStats); // Para depuración

    try {
        const response = await fetch('../api/jugadorPartidoAPI.php', { // ¡NUEVA API!
            method: 'POST', 
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(jugadoresStats)
        });
        const result = await response.json();

        if (result.success) {
            mostrarNotificacion(result.message || 'Estadísticas guardadas exitosamente.');
            cerrarModalPartido(); // Cierra el modal al finalizar
            mostrarPasoModal(1); // Reinicia a Paso 1
            obtenerPartidos(filterCategoriaSelect.value === '' ? null : filterCategoriaSelect.value); // Recarga tabla principal
        } else {
            mostrarNotificacion(result.message || 'Error al guardar estadísticas.', 'error');
            console.error('Error al guardar estadísticas:', result.message);
        }
    } catch (error) {
        mostrarNotificacion('Error de comunicación al guardar estadísticas.', 'error');
        console.error('Error en la petición API jugadorPartido:', error);
    }
});


// Manejar el envío del formulario de partido (handleFormPartidoSubmit ya maneja los pasos)
if (formPartido) formPartido.addEventListener('submit', handleFormPartidoSubmit);

// Filtro por categoría (se mantiene igual)
if (filterCategoriaSelect) {
    filterCategoriaSelect.addEventListener('change', (event) => {
        const selectedCategoryId = event.target.value;
        obtenerPartidos(selectedCategoryId === '' ? null : selectedCategoryId);
    });
}

// ============================
// INICIALIZACIÓN
// ============================
document.addEventListener('DOMContentLoaded', () => {
    // Cargar categorías primero, luego partidos
    obtenerCategorias().then(() => {
        obtenerPartidos(); // Cargar todos los partidos inicialmente
    });
});