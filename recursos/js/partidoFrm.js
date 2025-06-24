// ============================
// VARIABLES GLOBALES
// ============================
let allPartidos = [];
let allCategorias = [];
let partidoEditandoId = null;

// Referencias a elementos del layout
const menuToggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');
const profileToggle = document.getElementById('profileToggle');
const profileDropdown = document.getElementById('profileDropdown');

// Referencias a elementos de la vista de Partidos
const tablaPartidosBody = document.getElementById('tablaPartidos'); // <-- Coge el tbody para delegación de eventos
const totalPartidosSpan = document.getElementById('totalPartidos');
const filterCategoriaSelect = document.getElementById('filterCategoria');
const btnNuevoPartido = document.getElementById('btnNuevoPartido');
const btnDescargarInforme = document.getElementById('btnDescargarInforme');

// Referencias al modal de Partido (Nuevo/Editar)
const modalPartido = document.getElementById('modalPartido');
const formPartido = document.getElementById('formPartido');
const modalTitle = document.getElementById('modalTitle');
const btnCancelarModal = document.getElementById('btnCancelar');
const selectCategoriaModal = document.getElementById('id_categoria');
const partidoIdInput = document.getElementById('partidoId');

// Referencias a elementos de los pasos del modal (Nuevo/Editar)
const paso1InfoPartido = document.getElementById('paso1InfoPartido');
const paso2EstadisticasJugadores = document.getElementById('paso2EstadisticasJugadores');

// Referencias a los botones de navegación de los pasos (Nuevo/Editar)
const btnGuardarPaso1 = document.getElementById('btnGuardarPaso1');
const btnAtrasPaso2 = document.getElementById('btnAtrasPaso2');
const btnGuardarPaso2 = document.getElementById('btnGuardarPaso2');

// Referencia a la tabla de estadísticas de jugadores DENTRO del modal de Nuevo/Editar Partido (Paso 2)
const tablaEstadisticasJugadores = document.getElementById('tablaEstadisticasJugadores');

// ============================
// REFERENCIAS GLOBALES PARA MODALES ADICIONALES (obtenidas en DOMContentLoaded)
// ============================
let modalDetallePartido;
let btnCloseDetalleModal;
let detalleFechaHora;
let detalleRival;
let detalleLocalia;
let detalleResultado;
let detalleCategoria;
let tablaDetalleJugadoresStats;

// Referencias para el nuevo modal de informe por categoría
let modalInformeCategoria;
let btnCloseInformeCategoriaModal;
let selectCategoriaInforme; // El select de categoría dentro del modal de informe
let btnCancelarInformeCategoria;
let btnGenerarInforme;


// ============================
// FUNCIONES DE LA INTERFAZ (MENÚ Y DROPDOWN)
// ============================

function toggleMenu() {
    if (sidebar) sidebar.classList.toggle('open');
    if (overlay) overlay.classList.toggle('open');
}

function toggleProfile() {
    if (profileDropdown) profileDropdown.classList.toggle('open');
}

// ============================
// EVENT LISTENERS DEL MENÚ Y DROPDOWN
// ============================
if (menuToggle) menuToggle.addEventListener('click', toggleMenu);
if (overlay) overlay.addEventListener('click', toggleMenu);
if (profileToggle) profileToggle.addEventListener('click', toggleProfile);

document.addEventListener('click', (e) => {
    if (profileToggle && profileDropdown && !profileToggle.contains(e.target) && !profileDropdown.contains(e.target)) {
        profileDropdown.classList.remove('open');
    }
});

if (sidebar && overlay) {
    const menuItems = sidebar.querySelectorAll('.cursor-pointer');
    menuItems.forEach(item => {
        item.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('open');
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

const getCategoryNameById = (id) => {
    const categoria = allCategorias.find(cat => cat.id_categoria == id);
    return categoria ? categoria.nombre : 'Desconocida';
};

/**
 * Renderiza los partidos en la tabla HTML principal.
 * @param {Array} partidosToRender - Array de objetos de partido a mostrar.
 */
function renderizarTablaPartidos(partidosToRender) {
    if (!tablaPartidosBody) return;
    tablaPartidosBody.innerHTML = '';

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
        const [golesFavor, golesContra] = partido.resultado ? partido.resultado.split('-').map(Number) : [null, null];

        let estadoPartido = 'Pendiente';
        let resultadoColorClass = 'bg-blue-100 text-blue-800';

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
                        <button data-partido-id="${partido.id_partido}"
                                class="text-indigo-600 hover:text-indigo-900 transition-colors duration-200 px-3 py-1 rounded-md hover:bg-indigo-50 edit-button">
                            <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                            </svg>
                            Editar
                        </button>
                        <button data-partido-id="${partido.id_partido}" data-rival-nombre="${partido.rival}"
                                class="text-red-600 hover:text-red-900 transition-colors duration-200 px-3 py-1 rounded-md hover:bg-red-50 delete-button">
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
    mostrarPasoModal(1); // Asegurar que siempre empieza en el Paso 1
}

/**
 * Cierra el modal de partido.
 */
function cerrarModalPartido() {
    if (modalPartido) modalPartido.classList.add('hidden'); // Ocultar el modal
    if (formPartido) formPartido.reset(); // Resetear el formulario
    partidoEditandoId = null; // Limpiar el ID de partido en edición
    mostrarPasoModal(1); // Resetear a Paso 1 al cerrar
}

/**
 * Controla la visibilidad de los pasos del modal y los botones de navegación.
 * @param {number} paso - El paso que se debe mostrar (1 o 2).
 */
function mostrarPasoModal(paso) {
    const paso1InfoPartidoLocal = document.getElementById('paso1InfoPartido');
    const paso2EstadisticasJugadoresLocal = document.getElementById('paso2EstadisticasJugadores');
    const btnGuardarPaso1Local = document.getElementById('btnGuardarPaso1');
    const btnAtrasPaso2Local = document.getElementById('btnAtrasPaso2');
    const btnGuardarPaso2Local = document.getElementById('btnGuardarPaso2');
    const modalTitleLocal = document.getElementById('modalTitle');

    if (!paso1InfoPartidoLocal || !paso2EstadisticasJugadoresLocal || !btnGuardarPaso1Local || !btnAtrasPaso2Local || !btnGuardarPaso2Local || !modalTitleLocal) {
        console.error('Error: Elementos del modal de registro/edición no encontrados para controlar los pasos. Revisa el HTML.');
        return;
    }

    if (paso === 1) {
        paso1InfoPartidoLocal.classList.remove('hidden');
        paso2EstadisticasJugadoresLocal.classList.add('hidden');

        btnGuardarPaso1Local.classList.remove('hidden');
        btnAtrasPaso2Local.classList.add('hidden');
        btnGuardarPaso2Local.classList.add('hidden');

        modalTitleLocal.textContent = partidoEditandoId ? 'Editar Partido' : 'Nuevo Partido';
        btnGuardarPaso1Local.type = 'submit';
    } else if (paso === 2) {
        paso1InfoPartidoLocal.classList.add('hidden');
        paso2EstadisticasJugadoresLocal.classList.remove('hidden');

        btnGuardarPaso1Local.classList.add('hidden');
        btnAtrasPaso2Local.classList.remove('hidden');
        btnGuardarPaso2Local.classList.remove('hidden');

        modalTitleLocal.textContent = 'Registrar Estadísticas';
        btnGuardarPaso1Local.type = 'button';
    }
}


/**
 * Obtiene las categorías desde la API y las carga en los selects.
 */
async function obtenerCategorias() {
    try {
        const response = await fetch('../api/categoriaAPI.php');
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        const data = await response.json();
        allCategorias = data;

        // Limpiar y rellenar el select de filtro de categoría principal
        if (filterCategoriaSelect) {
            filterCategoriaSelect.innerHTML = '<option value="">Todas las categorías</option>';
            allCategorias.forEach(categoria => {
                const optionFilter = document.createElement('option');
                optionFilter.value = categoria.id_categoria;
                optionFilter.textContent = categoria.nombre;
                filterCategoriaSelect.appendChild(optionFilter);
            });
        }

        // Limpiar y rellenar el select de categoría en el modal de Nuevo/Editar Partido
        if (selectCategoriaModal) {
            selectCategoriaModal.innerHTML = '<option value="">Seleccionar categoría...</option>';
            allCategorias.forEach(categoria => {
                const optionModal = document.createElement('option');
                optionModal.value = categoria.id_categoria;
                optionModal.textContent = `${categoria.nombre} (${categoria.edad_minima}-${categoria.edad_maxima} años)`;
                selectCategoriaModal.appendChild(optionModal);
            });
        }

        // Limpiar y rellenar el select de categoría en el modal de Informe (si existe)
        if (selectCategoriaInforme) {
            selectCategoriaInforme.innerHTML = '<option value="">Todas las categorías</option>'; // Opción para informe general
            allCategorias.forEach(categoria => {
                const optionInforme = document.createElement('option');
                optionInforme.value = categoria.id_categoria;
                optionInforme.textContent = categoria.nombre;
                selectCategoriaInforme.appendChild(optionInforme);
            });
        }

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
    let url = '../api/partidosAPI.php';
    // Si se pasa un ID de categoría y no es el valor por defecto "0" (Todas)
    if (id_categoria !== null && id_categoria !== "" && id_categoria !== "0") {
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
    if (!tablaEstadisticasJugadores) return;

    tablaEstadisticasJugadores.innerHTML = `
        <tr>
            <td colspan="6" class="px-6 py-4 text-center text-gray-500">Cargando jugadores...</td>
        </tr>
    `;
    try {
        const response = await fetch(`../api/jugadorAPI.php?id_categoria=${idCategoria}`);
        if (!response.ok) throw new Error('Error al cargar jugadores de la categoría.');
        const jugadoresCategoria = await response.json();

        tablaEstadisticasJugadores.innerHTML = '';
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
 */
async function handleFormPartidoSubmit(event) {
    event.preventDefault();

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

                if (!partidoEditandoId && result.id_partido) {
                    if (partidoIdInput) partidoIdInput.value = result.id_partido;
                    partidoEditandoId = result.id_partido;
                }

                const categoriaId = partidoData.id_categoria;
                await cargarJugadoresParaEstadisticas(partidoEditandoId, categoriaId);
                mostrarPasoModal(2);

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
        console.log("handleFormPartidoSubmit llamado desde Paso 2. Usar btnGuardarPaso2.");
    }
}

/**
 * Muestra los detalles de un partido en un modal con estadísticas de jugadores.
 * Se mantiene en window.verDetallePartido porque el onclick ya funciona.
 * @param {string} id - ID del partido a ver.
 */
window.verDetallePartido = async (id) => {
    const modalDetallePartidoLocal = document.getElementById('modalDetallePartido');
    const btnCloseDetalleModalLocal = document.getElementById('btnCloseDetalleModal');
    const detalleFechaHoraLocal = document.getElementById('detalleFechaHora');
    const detalleRivalLocal = document.getElementById('detalleRival');
    const detalleLocaliaLocal = document.getElementById('detalleLocalia');
    const detalleResultadoLocal = document.getElementById('detalleResultado');
    const detalleCategoriaLocal = document.getElementById('detalleCategoria');
    const tablaDetalleJugadoresStatsLocal = document.getElementById('tablaDetalleJugadoresStats');

    if (!modalDetallePartidoLocal || !btnCloseDetalleModalLocal || !detalleFechaHoraLocal || !detalleRivalLocal || !detalleLocaliaLocal || !detalleResultadoLocal || !detalleCategoriaLocal || !tablaDetalleJugadoresStatsLocal) {
        mostrarNotificacion('Error: Elementos del modal de detalle no encontrados en el HTML. Revisa los IDs y la estructura.', 'error');
        console.error('Error: Algunos IDs para el modal de detalle no se encontraron. Asegúrate de que el modal HTML está en el lugar correcto y sus IDs son correctos.');
        return;
    }

    // Limpiar y mostrar "Cargando..."
    detalleFechaHoraLocal.textContent = 'Cargando...';
    detalleRivalLocal.textContent = 'Cargando...';
    detalleLocaliaLocal.textContent = 'Cargando...';
    detalleResultadoLocal.textContent = 'Cargando...';
    detalleCategoriaLocal.textContent = 'Cargando...';
    tablaDetalleJugadoresStatsLocal.innerHTML = `
        <tr>
            <td colspan="5" class="px-6 py-4 text-center text-gray-500">Cargando estadísticas de jugadores...</td>
        </tr>
    `;
    modalDetallePartidoLocal.classList.remove('hidden'); // Mostrar el modal mientras carga

    try {
        const response = await fetch(`../api/partidosAPI.php?id_partido_detalle=${id}`);
        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Error al cargar los detalles del partido.');
        }
        const data = await response.json();

        if (data.success && data.partido) {
            const partido = data.partido;
            const jugadoresStats = data.jugadores_stats;

            // Llenar información general del partido
            detalleFechaHoraLocal.textContent = `${partido.fecha} ${partido.hora ? partido.hora.substring(0, 5) : 'N/A'}`;
            detalleRivalLocal.textContent = partido.rival;
            detalleLocaliaLocal.textContent = partido.local_visitante;
            detalleResultadoLocal.textContent = partido.resultado;
            detalleCategoriaLocal.textContent = partido.categoria_nombre || 'N/A';

            // Llenar tabla de estadísticas de jugadores
            tablaDetalleJugadoresStatsLocal.innerHTML = ''; // Limpiar
            if (jugadoresStats && jugadoresStats.length > 0) {
                jugadoresStats.forEach(jugador => {
                    const row = `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                ${jugador.nombre} ${jugador.apellido} (${jugador.dorsal ? 'Dorsal: ' + jugador.dorsal : 'N/A'} / ${jugador.posicion || 'N/A'})
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">${jugador.minutos_jugados || 0}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">${jugador.goles || 0}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">${jugador.tarjetas_amarillas || 0}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">${jugador.tarjetas_rojas || 0}</td>
                        </tr>
                    `;
                    tablaDetalleJugadoresStatsLocal.innerHTML += row;
                });
            } else {
                tablaDetalleJugadoresStatsLocal.innerHTML = `
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">No hay estadísticas de jugadores para este partido.</td>
                    </tr>
                `;
            }

        } else {
            mostrarNotificacion(data.message || 'Error al obtener los detalles del partido.', 'error');
            modalDetallePartidoLocal.classList.add('hidden'); // Ocultar si hay error
        }

    } catch (error) {
        console.error('Error en fetch verDetallePartido:', error);
        mostrarNotificacion(`Error al cargar detalles del partido: ${error.message}.`, 'error');
        modalDetallePartidoLocal.classList.add('hidden'); // Ocultar si hay error
    }
};

/**
 * Lógica para editar un partido existente.
 * NO es una función global de window, se llama internamente por la delegación de eventos.
 * @param {string} id - ID del partido a editar.
 */
async function handleEditarPartido(id) {
    partidoEditandoId = id;
    modalTitle.textContent = 'Editar Partido';
    formPartido.reset(); // Limpiar el formulario
    if (partidoIdInput) partidoIdInput.value = id; // Setear el ID del partido

    // Volver al paso 1 al editar
    mostrarPasoModal(1);

    try {
        const response = await fetch(`../api/partidosAPI.php?id_partido_detalle=${id}`); // Reutilizamos la API de detalle
        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Error al cargar los datos del partido para editar.');
        }
        const data = await response.json();

        if (data.success && data.partido) {
            const partido = data.partido;
            // Llenar los campos del formulario con los datos del partido
            document.getElementById('fecha').value = partido.fecha;
            document.getElementById('hora').value = partido.hora.substring(0, 5); // Solo hora y minutos
            document.getElementById('rival').value = partido.rival;
            document.getElementById('localia').value = partido.local_visitante;
            document.getElementById('id_categoria').value = partido.id_categoria;
            const [golesFavor, golesContra] = partido.resultado.split('-').map(Number);
            document.getElementById('goles_favor').value = golesFavor;
            document.getElementById('goles_contra').value = golesContra;

            if (modalPartido) modalPartido.classList.remove('hidden'); // Mostrar el modal
        } else {
            mostrarNotificacion(data.message || 'No se pudieron cargar los datos del partido para editar.', 'error');
        }
    } catch (error) {
        console.error('Error al cargar datos para edición:', error);
        mostrarNotificacion(`Error al cargar datos del partido para editar: ${error.message}.`, 'error');
    }
}


/**
 * Lógica para eliminar un partido.
 * NO es una función global de window, se llama internamente por la delegación de eventos.
 * @param {string} id - ID del partido a eliminar.
 * @param {string} rival - Nombre del rival para la confirmación.
 */
async function handleEliminarPartido(id, rival) {
    if (confirm(`¿Estás seguro de que quieres eliminar el partido contra "${rival}"? Esto también eliminará las estadísticas de jugadores asociadas.`)) {
        try {
            const response = await fetch(`../api/partidosAPI.php?id_partido=${id}`, {
                method: 'DELETE'
            });
            const result = await response.json();

            if (result.success) {
                mostrarNotificacion('Partido eliminado exitosamente.');
                obtenerPartidos(filterCategoriaSelect.value === '' ? null : filterCategoriaSelect.value); // Recargar la tabla
            } else {
                mostrarNotificacion(result.message || 'Error al eliminar el partido.', 'error');
                console.error('Error al eliminar partido:', result.message);
            }
        } catch (error) {
            mostrarNotificacion('Error de comunicación al eliminar el partido.', 'error');
            console.error('Error en la petición API al eliminar partido:', error);
        }
    }
}


// ============================
// EVENT LISTENERS DE LA VISTA PARTIDOS
// ============================

// Botones de acción del panel
if (btnNuevoPartido) btnNuevoPartido.addEventListener('click', () => {
    abrirModalNuevoPartido();
});

// LÓGICA PARA btnDescargarInforme - ABRIR EL MODAL DE SELECCIÓN DE CATEGORÍA
if (btnDescargarInforme) {
    btnDescargarInforme.addEventListener('click', () => {
        if (modalInformeCategoria) {
            // Asegúrate de que el select de categorías para el informe esté actualizado
            // Re-llenar el select de informe por si hay nuevas categorías
            if (selectCategoriaInforme) {
                selectCategoriaInforme.innerHTML = '<option value="">Todas las categorías</option>'; // Opción para informe general
                allCategorias.forEach(categoria => {
                    const optionInforme = document.createElement('option');
                    optionInforme.value = categoria.id_categoria;
                    optionInforme.textContent = categoria.nombre;
                    selectCategoriaInforme.appendChild(optionInforme);
                });
            }
            modalInformeCategoria.classList.remove('hidden'); // Mostrar el modal
        } else {
            mostrarNotificacion('Error: No se encontró el modal para seleccionar categoría del informe.', 'error');
            console.error('Modal de informe por categoría (modalInformeCategoria) no encontrado.');
        }
    });
}


// Botones del modal (btnCancelarModal se mantiene igual, cierra el modal)
if (btnCancelarModal) btnCancelarModal.addEventListener('click', () => {
    cerrarModalPartido();
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
    if (tablaEstadisticasJugadores) {
        const filasJugadores = tablaEstadisticasJugadores.querySelectorAll('tr[data-jugador-id]');

        filasJugadores.forEach(fila => {
            const jugadorId = fila.dataset.jugadorId;
            const jugoCheckbox = fila.querySelector(`input[name="jugo_${jugadorId}"]`);

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

    console.log("Datos de estadísticas a enviar:", jugadoresStats);

    try {
        const response = await fetch('../api/jugadorPartidoAPI.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(jugadoresStats)
        });
        const result = await response.json();

        if (result.success) {
            mostrarNotificacion(result.message || 'Estadísticas guardadas exitosamente.');
            cerrarModalPartido();
            obtenerPartidos(filterCategoriaSelect.value === '' ? null : filterCategoriaSelect.value);
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
// INICIALIZACIÓN Y LISTENERS GLOBALES (se ejecutan una vez al cargar el DOM)
// ============================

document.addEventListener('DOMContentLoaded', () => {
    // Referencias para el modal de detalle de partido
    modalDetallePartido = document.getElementById('modalDetallePartido');
    btnCloseDetalleModal = document.getElementById('btnCloseDetalleModal');

    // Referencias para el modal de informe (asegúrate de que existen)
    modalInformeCategoria = document.getElementById('modalInformeCategoria');
    btnCloseInformeCategoriaModal = document.getElementById('btnCloseInformeCategoriaModal');
    selectCategoriaInforme = document.getElementById('selectCategoriaInforme');
    btnCancelarInformeCategoria = document.getElementById('btnCancelarInformeCategoria');
    btnGenerarInforme = document.getElementById('btnGenerarInforme');

    // Cargar categorías primero, luego partidos
    obtenerCategorias().then(() => {
        obtenerPartidos();
    });

    // Listener para cerrar el modal de detalle de partido
    if (modalDetallePartido && btnCloseDetalleModal) {
        btnCloseDetalleModal.addEventListener('click', () => {
            modalDetallePartido.classList.add('hidden');
        });
        modalDetallePartido.addEventListener('click', (e) => {
            if (e.target === modalDetallePartido) {
                modalDetallePartido.classList.add('hidden');
            }
        });
    }

    // Listener para cerrar el modal de selección de categoría para informe
    if (modalInformeCategoria && btnCloseInformeCategoriaModal) {
        btnCloseInformeCategoriaModal.addEventListener('click', () => {
            modalInformeCategoria.classList.add('hidden');
        });
    }
    if (modalInformeCategoria && btnCancelarInformeCategoria) {
        btnCancelarInformeCategoria.addEventListener('click', () => {
            modalInformeCategoria.classList.add('hidden');
        });
    }

    // LÓGICA PRINCIPAL DEL BOTÓN GENERAR INFORME DEL MODAL
    if (btnGenerarInforme) {
        btnGenerarInforme.addEventListener('click', () => {
            const categoriaIdParaInforme = selectCategoriaInforme.value; // Puede ser "" para todas las categorías
            const url = `../api/partidosAPI.php?get_for_report=1&id_categoria=${categoriaIdParaInforme}`;

            mostrarNotificacion('Generando informe PDF...', 'info');

            // Redirige el navegador a la URL del script PHP que generará el PDF.
            // Esto hace que el navegador descargue el archivo directamente.
            window.location.href = url;

            // Oculta el modal de selección de categoría inmediatamente.
            if (modalInformeCategoria) {
                modalInformeCategoria.classList.add('hidden');
            }
        });
    }

    // ************************************************
    // ***** DELEGACIÓN DE EVENTOS PARA EDITAR Y ELIMINAR *****
    // Este listener se adjunta UNA SOLA VEZ al cuerpo de la tabla (tablaPartidosBody).
    // Escucha los clics en los botones 'Editar' y 'Eliminar' dinámicamente.
    // ************************************************
    if (tablaPartidosBody) {
        tablaPartidosBody.addEventListener('click', (event) => {
            // Usa .closest() para encontrar el botón que fue clicado, o un ancestro con la clase específica.
            // Esto es más robusto que event.target si el clic es en un icono dentro del botón.
            const targetButton = event.target.closest('button');

            // Asegurarse de que se hizo clic en un botón válido y no en otra parte de la tabla.
            if (targetButton) {
                const partidoId = targetButton.dataset.partidoId; // Obtiene el ID del data-attribute
                const rivalNombre = targetButton.dataset.rivalNombre; // Obtiene el nombre del rival del data-attribute

                if (targetButton.classList.contains('edit-button')) {
                    // Llamar a la función que maneja la edición
                    handleEditarPartido(partidoId);
                } else if (targetButton.classList.contains('delete-button')) {
                    // Llamar a la función que maneja la eliminación
                    handleEliminarPartido(partidoId, rivalNombre);
                }
            }
        });
    }
});