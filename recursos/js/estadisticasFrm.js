// ============================
// VARIABLES GLOBALES
// ============================
let allEstadisticas = []; // Para almacenar todas las estadísticas cargadas
let allCategorias = []; // Para las categorías en el filtro

// Referencias a elementos del layout (replicadas de partidoFrm.js)
const menuToggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');
const profileToggle = document.getElementById('profileToggle');
const profileDropdown = document.getElementById('profileDropdown');

// Referencias a elementos de la vista de Plantilla
const tablaEstadisticasPlantilla = document.getElementById('tablaEstadisticasPlantilla');
const totalJugadoresStatsSpan = document.getElementById('totalJugadoresStats');
const filterCategoriaSelect = document.getElementById('filterCategoria');
const sortOrderSelect = document.getElementById('sortOrder');
const btnDescargarInforme = document.getElementById('btnDescargarInforme'); // Botón de descarga si lo implementas

// ============================
// FUNCIONES DE LA INTERFAZ (MENÚ Y DROPDOWN) - REPLICADAS
// ============================

function toggleMenu() {
    if (sidebar) sidebar.classList.toggle('open');
    if (overlay) overlay.classList.toggle('open');
}

function toggleProfile() {
    if (profileDropdown) profileDropdown.classList.toggle('open');
}

// ============================
// EVENT LISTENERS DEL MENÚ Y DROPDOWN - REPLICADOS
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
// FUNCIONES DE NOTIFICACIÓN - REPLICADAS
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
// FUNCIONES PARA LA VISTA DE PLANTILLA (ESTADÍSTICAS)
// ============================

const getCategoryNameById = (id) => {
    const categoria = allCategorias.find(cat => cat.id_categoria == id);
    return categoria ? categoria.nombre : 'Desconocida';
};

/**
 * Renderiza las estadísticas de los jugadores en la tabla HTML.
 * @param {Array} estadisticasToRender - Array de objetos de estadísticas a mostrar.
 */
function renderizarTablaEstadisticas(estadisticasToRender) {
    if (!tablaEstadisticasPlantilla) return;
    tablaEstadisticasPlantilla.innerHTML = ''; // Limpiar la tabla

    if (estadisticasToRender.length === 0) {
        tablaEstadisticasPlantilla.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                    <div class="flex flex-col items-center">
                        <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                        <p class="text-lg font-medium">No hay estadísticas para mostrar.</p>
                        <p class="text-sm">Asegúrate de haber registrado partidos y estadísticas de jugadores.</p>
                    </div>
                </td>
            </tr>
        `;
        if (totalJugadoresStatsSpan) totalJugadoresStatsSpan.textContent = 0;
        return;
    }

    estadisticasToRender.forEach(stat => {
        const row = `
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                            <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-400 to-green-500 flex items-center justify-center">
                                <span class="text-sm font-medium text-white">${stat.nombre.charAt(0)}${stat.apellido.charAt(0)}</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">${stat.nombre} ${stat.apellido}</div>
                            <div class="text-sm text-gray-500">Dorsal: ${stat.dorsal || 'N/A'} - ${stat.posicion || 'Sin posición'}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                        ${stat.categoria_nombre || 'Sin categoría'}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-gray-900">${stat.goles_totales}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-gray-900">${stat.partidos_jugados}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-gray-900">${stat.minutos_jugados}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-gray-900">${stat.tarjetas_amarillas}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-gray-900">${stat.tarjetas_rojas}</td>
            </tr>
        `;
        tablaEstadisticasPlantilla.innerHTML += row;
    });
    if (totalJugadoresStatsSpan) totalJugadoresStatsSpan.textContent = estadisticasToRender.length;
}

/**
 * Obtiene las categorías (para el filtro) desde la API.
 */
async function obtenerCategoriasParaFiltro() {
    try {
        const response = await fetch('../api/categoriaAPI.php'); // Reutiliza tu API de categorías
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        const data = await response.json();
        allCategorias = data; // Almacenar categorías globalmente

        if (filterCategoriaSelect) {
            filterCategoriaSelect.innerHTML = '<option value="">Todas las categorías</option>';
            allCategorias.forEach(categoria => {
                const option = document.createElement('option');
                option.value = categoria.id_categoria;
                option.textContent = categoria.nombre;
                filterCategoriaSelect.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error al cargar categorías para filtro:', error);
        mostrarNotificacion('Error al cargar las categorías para filtro.', 'error');
    }
}

/**
 * Carga las estadísticas de los jugadores desde la API y las renderiza.
 * Aplica filtros y ordenamiento si se especifican.
 * @param {string|null} id_categoria - ID de la categoría para filtrar (0 para todas).
 * @param {string} ordenar_por - Campo y dirección para ordenar (ej. 'goles_totales_desc').
 */
async function obtenerEstadisticas(id_categoria = 0, ordenar_por = '') {
    let url = `../api/estadisticasAPI.php?id_categoria=${id_categoria}&ordenar_por=${ordenar_por}`;
    
    if (tablaEstadisticasPlantilla) {
      tablaEstadisticasPlantilla.innerHTML = `
        <tr>
            <td colspan="7" class="px-6 py-8 text-center text-gray-500">Cargando estadísticas...</td>
        </tr>
      `;
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
        allEstadisticas = data; // Guardar todas las estadísticas (filtradas/ordenadas)
        renderizarTablaEstadisticas(allEstadisticas);
    } catch (error) {
        console.error('Error al cargar estadísticas:', error);
        if (tablaEstadisticasPlantilla) {
            tablaEstadisticasPlantilla.innerHTML = `
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-red-500">Error al cargar estadísticas: ${error.message}</td>
                </tr>
            `;
        }
        mostrarNotificacion(`Error al cargar las estadísticas: ${error.message || error}.`, 'error');
    }
}

// Función para manejar la descarga del informe (PDF)
// Requiere jspdf y jspdf-autotable en el HTML
if (btnDescargarInforme) {
    btnDescargarInforme.addEventListener('click', () => {
        // Asegúrate de que window.jspdf.jsPDF esté disponible
        if (typeof window.jspdf === 'undefined' || typeof window.jspdf.jsPDF === 'undefined') {
            mostrarNotificacion('Librería jsPDF no cargada. No se puede generar el informe.', 'error');
            console.error('jsPDF library not loaded.');
            return;
        }

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        // Definir las columnas de la tabla del PDF
        const head = [['Jugador', 'Categoría', 'Goles', 'Partidos', 'Minutos', 'TA', 'TR']];
        
        // Mapear los datos de las estadísticas a un formato que jspdf-autotable pueda usar
        const body = allEstadisticas.map(stat => [
            `${stat.nombre} ${stat.apellido} (${stat.dorsal || 'N/A'})`,
            stat.categoria_nombre || 'N/A',
            stat.goles_totales,
            stat.partidos_jugados,
            stat.minutos_jugados,
            stat.tarjetas_amarillas,
            stat.tarjetas_rojas
        ]);

        doc.setFontSize(18);
        doc.text("Informe de Estadísticas de la Plantilla", 14, 22);
        doc.setFontSize(11);
        doc.setTextColor(100);

        // Generar la tabla en el PDF
        doc.autoTable({
            head: head,
            body: body,
            startY: 30,
            theme: 'striped', // 'striped', 'grid', 'plain'
            headStyles: { fillColor: [39, 174, 96] }, // Color verde para la cabecera
            margin: { top: 10, right: 10, bottom: 10, left: 10 },
            didDrawPage: function (data) {
                // Footer
                let str = "Página " + doc.internal.getNumberOfPages();
                doc.setFontSize(10);
                doc.text(str, data.settings.margin.left, doc.internal.pageSize.height - 10);
            }
        });

        doc.save('informe_plantilla_estadisticas.pdf');
        mostrarNotificacion('Informe PDF generado y descargado.', 'success');
    });
}


// ============================
// EVENT LISTENERS DE LA VISTA PLANTILLA
// ============================

if (filterCategoriaSelect) {
    filterCategoriaSelect.addEventListener('change', () => {
        const selectedCategoryId = filterCategoriaSelect.value === '' ? 0 : parseInt(filterCategoriaSelect.value);
        const currentSortOrder = sortOrderSelect ? sortOrderSelect.value : '';
        obtenerEstadisticas(selectedCategoryId, currentSortOrder);
    });
}

if (sortOrderSelect) {
    sortOrderSelect.addEventListener('change', () => {
        const currentCategoryId = filterCategoriaSelect ? (filterCategoriaSelect.value === '' ? 0 : parseInt(filterCategoriaSelect.value)) : 0;
        const selectedSortOrder = sortOrderSelect.value;
        obtenerEstadisticas(currentCategoryId, selectedSortOrder);
    });
}


// ============================
// INICIALIZACIÓN DE LA VISTA
// ============================
document.addEventListener('DOMContentLoaded', () => {
    // Cargar categorías para el filtro primero
    obtenerCategoriasParaFiltro().then(() => {
        // Luego cargar las estadísticas iniciales (sin filtro, sin orden)
        obtenerEstadisticas(0, '');
    });
});