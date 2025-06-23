// ============================
// VARIABLES GLOBALES
// ============================
let jugadores = [];
let jugadorEditando = null;

const menuToggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');
const profileToggle = document.getElementById('profileToggle');
const profileDropdown = document.getElementById('profileDropdown');

const tablaJugadores = document.getElementById('tablaJugadores');
const totalJugadores = document.getElementById('totalJugadores');
const searchInput = document.getElementById('searchInput');
const filterPosicion = document.getElementById('filterPosicion');
const filterGenero = document.getElementById('filterGenero');
const filterCategoria = document.getElementById('filterCategoria'); 

const modalJugador = document.getElementById('modalJugador');
const formJugador = document.getElementById('formJugador');
const modalTitle = document.getElementById('modalTitle');

// Botones
const btnNuevoJugador = document.getElementById('btnNuevoJugador');
const btnCancelar = document.getElementById('btnCancelar');

function toggleMenu() {
    sidebar.classList.toggle('open');
    overlay.classList.toggle('open');
}

function toggleProfile() {
    profileDropdown.classList.toggle('open');
}

// ============================
// EVENT LISTENERS DEL MENÚ
// ============================
menuToggle.addEventListener('click', toggleMenu);
overlay.addEventListener('click', toggleMenu);
profileToggle.addEventListener('click', toggleProfile);

// Cerrar dropdown del perfil al hacer clic fuera
document.addEventListener('click', (e) => {
    if (!profileToggle.contains(e.target) && !profileDropdown.contains(e.target)) {
        profileDropdown.classList.remove('open');
    }
});

// Cerrar menú al hacer clic en una opción
const menuItems = sidebar.querySelectorAll('.cursor-pointer');
menuItems.forEach(item => {
    item.addEventListener('click', () => {
        sidebar.classList.remove('open');
        overlay.classList.remove('open');
    });
});

// ============================
// FUNCIONES PRINCIPALES
// ============================

// Función para mostrar notificaciones
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

// Función para calcular la edad
function calcularEdad(fechaNacimiento) {
    const hoy = new Date();
    const nacimiento = new Date(fechaNacimiento);
    let edad = hoy.getFullYear() - nacimiento.getFullYear();
    const mes = hoy.getMonth() - nacimiento.getMonth();
    
    if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
        edad--;
    }
    
    return edad;
}

// FUNCIÓN MODIFICADA: Ahora usa filtros del backend
async function obtenerJugadores(filtros = {}) {
    try {
        let url = '../api/jugadorAPI.php';
        
        // Si hay filtros, construir URL con parámetros
        if (Object.keys(filtros).length > 0) {
            url += '?filtrar=1';
            
            if (filtros.busqueda) {
                url += `&busqueda=${encodeURIComponent(filtros.busqueda)}`;
            }
            if (filtros.posicion) {
                url += `&posicion=${encodeURIComponent(filtros.posicion)}`;
            }
            if (filtros.genero) {
                url += `&genero=${encodeURIComponent(filtros.genero)}`;
            }
            if (filtros.categoria) {
                url += `&categoria=${encodeURIComponent(filtros.categoria)}`;
            }
        }
        
        const response = await fetch(url);
        if (!response.ok) throw new Error('Error en la respuesta del servidor');
        
        const data = await response.json();
        jugadores = data;
        renderizarTabla(jugadores);
        actualizarContador();
    } catch (error) {
        console.error('Error al cargar jugadores:', error);
        mostrarNotificacion('Error al cargar los jugadores', 'error');
    }
}

// Función para obtener las categorías disponibles
async function obtenerCategorias() {
    try {
        const response = await fetch('../api/categoriaAPI.php');
        if (!response.ok) throw new Error('Error en la respuesta del servidor');
        
        const categorias = await response.json();
        
        // Llenar el select de categorías en el modal
        const selectCategoria = document.getElementById('id_categoria');
        selectCategoria.innerHTML = '<option value="">Seleccionar categoría...</option>';
        
        // Llenar el filtro de categorías
        const filterCategoriaSelect = document.getElementById('filterCategoria');
        filterCategoriaSelect.innerHTML = '<option value="">Todas las categorías</option>';
        
        categorias.forEach(categoria => {
            // Para el modal
            const option = document.createElement('option');
            option.value = categoria.id_categoria;
            option.textContent = `${categoria.nombre} (${categoria.edad_minima}-${categoria.edad_maxima} años)`;
            selectCategoria.appendChild(option);
            
            // Para el filtro
            const filterOption = document.createElement('option');
            filterOption.value = categoria.nombre; // Usamos el nombre para filtrar
            filterOption.textContent = categoria.nombre;
            filterCategoriaSelect.appendChild(filterOption);
        });
        
    } catch (error) {
        console.error('Error al cargar categorías:', error);
        mostrarNotificacion('Error al cargar las categorías', 'error');
    }
}

// Función para renderizar la tabla
function renderizarTabla(datos) {
    tablaJugadores.innerHTML = '';
    
    if (datos.length === 0) {
        tablaJugadores.innerHTML = `
            <tr>
                <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                    <div class="flex flex-col items-center">
                        <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                        <p class="text-lg font-medium">No hay jugadores registrados</p>
                        <p class="text-sm">Agrega el primer jugador para comenzar</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }

    datos.forEach(jugador => {
        const fila = document.createElement('tr');
        fila.className = 'hover:bg-gray-50 transition-colors';
        
        // Calcular edad si existe fecha de nacimiento
        const edad = jugador.fecha_nacimiento ? calcularEdad(jugador.fecha_nacimiento) : jugador.edad || 'N/A';
        
        // Formatear información física
        const infoFisica = [];
        if (jugador.altura) infoFisica.push(`${jugador.altura}m`);
        if (jugador.peso) infoFisica.push(`${jugador.peso}kg`);
        const fisico = infoFisica.length > 0 ? infoFisica.join(' / ') : 'No especificado';
        
        fila.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                        <div class="h-10 w-10 rounded-full bg-gradient-to-r from-green-400 to-blue-500 flex items-center justify-center">
                            <span class="text-sm font-medium text-white">${jugador.nombre.charAt(0)}${jugador.apellido.charAt(0)}</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">${jugador.nombre} ${jugador.apellido}</div>
                        <div class="text-sm text-gray-500">ID: ${jugador.cedula || 'N/A'}</div>
                        ${jugador.telefono ? `<div class="text-sm text-gray-500">${jugador.telefono}</div>` : ''}
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-1 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                    </svg>
                    ${edad} años
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                    jugador.posicion === 'Portero' ? 'bg-yellow-100 text-yellow-800' :
                    jugador.posicion && jugador.posicion.includes('Defensa') ? 'bg-blue-100 text-blue-800' :
                    jugador.posicion && jugador.posicion.includes('Medio') ? 'bg-green-100 text-green-800' :
                    jugador.posicion && jugador.posicion.includes('Delantero') ? 'bg-red-100 text-red-800' :
                    jugador.posicion && jugador.posicion.includes('Extremo') ? 'bg-purple-100 text-purple-800' :
                    'bg-gray-100 text-gray-800'
                }">
                    ${jugador.posicion || 'No especificada'}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <div class="flex items-center justify-center">
                    ${jugador.dorsal ? `
                        <div class="w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center font-bold text-sm">
                            ${jugador.dorsal}
                        </div>
                    ` : '<span class="text-gray-400">Sin dorsal</span>'}
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${fisico}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                    ${jugador.categoria || 'Sin asignar'}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                    jugador.genero === 'Masculino' ? 'bg-blue-100 text-blue-800' : 'bg-pink-100 text-pink-800'
                }">
                    ${jugador.genero || 'No especificado'}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                    <button onclick="editarJugador('${jugador.id_jugador}')" 
                            class="text-indigo-600 hover:text-indigo-900 transition-colors duration-200 px-3 py-1 rounded-md hover:bg-indigo-50">
                        <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                        </svg>
                        Editar
                    </button>
                    <button onclick="eliminarJugador('${jugador.id_jugador}', '${jugador.nombre} ${jugador.apellido}')" 
                            class="text-red-600 hover:text-red-900 transition-colors duration-200 px-3 py-1 rounded-md hover:bg-red-50">
                        <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"></path>
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 012 0v4a1 1 0 11-2 0V7zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V7a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        Eliminar
                    </button>
                </div>
            </td>
        `;
        
        tablaJugadores.appendChild(fila);
    });
}

// Función para actualizar contador
function actualizarContador() {
    totalJugadores.textContent = jugadores.length;
}

// NUEVA FUNCIÓN: Aplicar filtros usando el backend
function aplicarFiltros() {
    const filtros = {};
    
    // Obtener valores de los filtros
    const busqueda = searchInput.value.trim();
    const posicion = filterPosicion.value;
    const genero = filterGenero.value;
    const categoria = filterCategoria.value;
    
    // Solo agregar filtros que tengan valor
    if (busqueda) filtros.busqueda = busqueda;
    if (posicion) filtros.posicion = posicion;
    if (genero) filtros.genero = genero;
    if (categoria) filtros.categoria = categoria;
    
    // Llamar a obtenerJugadores con los filtros
    obtenerJugadores(filtros);
}

// FUNCIÓN OBSOLETA: Ya no se usa porque ahora filtramos en el backend
// function filtrarJugadores() { ... }

// Función para abrir modal de nuevo jugador
function abrirModalNuevo() {
    jugadorEditando = null;
    modalTitle.textContent = 'Nuevo Jugador';
    formJugador.reset();
    modalJugador.classList.remove('hidden');
}

// Función para cerrar modal
function cerrarModal() {
    modalJugador.classList.add('hidden');
    formJugador.reset();
    jugadorEditando = null;
}

// Función para editar jugador
function editarJugador(id) {
    const jugador = jugadores.find(j => j.id_jugador == id);
    if (!jugador) {
        mostrarNotificacion('Jugador no encontrado', 'error');
        return;
    }
    
    jugadorEditando = id;
    modalTitle.textContent = 'Editar Jugador';
    
    // Llenar el formulario con los datos del jugador
    document.getElementById('cedula').value = jugador.cedula || '';
    document.getElementById('nombre').value = jugador.nombre || '';
    document.getElementById('apellido').value = jugador.apellido || '';
    document.getElementById('fecha_nacimiento').value = jugador.fecha_nacimiento || '';
    document.getElementById('telefono').value = jugador.telefono || '';
    document.getElementById('genero').value = jugador.genero || '';
    document.getElementById('altura').value = jugador.altura || '';
    document.getElementById('peso').value = jugador.peso || '';
    document.getElementById('posicion').value = jugador.posicion || '';
    document.getElementById('dorsal').value = jugador.dorsal || '';
    document.getElementById('fecha_ingreso').value = jugador.fecha_ingreso || '';
    document.getElementById('id_categoria').value = jugador.id_categoria || '';
    
    modalJugador.classList.remove('hidden');
}

// Función para eliminar jugador
function eliminarJugador(id, nombre) {
    if (confirm(`¿Estás seguro de que deseas eliminar al jugador ${nombre}?`)) {
        fetch(`../api/jugadorAPI.php?id=${id}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarNotificacion('Jugador eliminado correctamente');
                // Recargar con los filtros actuales
                aplicarFiltros();
            } else {
                mostrarNotificacion('Error al eliminar jugador', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarNotificacion('Error al eliminar jugador', 'error');
        });
    }
}

// ============================
// EVENT LISTENERS
// ============================

// MODIFICADO: Ahora usa aplicarFiltros() en lugar de filtrarJugadores()
let timeoutId;

// Función para debounce en la búsqueda (evita hacer muchas consultas mientras se escribe)
function debounce(func, delay) {
    return function(...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => func.apply(this, args), delay);
    };
}

// Event listeners para filtros - Usar debounce en el campo de búsqueda
searchInput.addEventListener('input', debounce(aplicarFiltros, 500)); // 500ms de delay
filterPosicion.addEventListener('change', aplicarFiltros);
filterGenero.addEventListener('change', aplicarFiltros);
filterCategoria.addEventListener('change', aplicarFiltros);

// Botones principales
btnNuevoJugador.addEventListener('click', abrirModalNuevo);
btnCancelar.addEventListener('click', cerrarModal);

// Cerrar modal al hacer clic fuera
modalJugador.addEventListener('click', (e) => {
    if (e.target === modalJugador) cerrarModal();
});

// Auto-calcular edad cuando se cambia la fecha de nacimiento
document.getElementById('fecha_nacimiento').addEventListener('change', function() {
    const fechaNacimiento = this.value;
    if (fechaNacimiento) {
        const edad = calcularEdad(fechaNacimiento);
        console.log('Edad calculada:', edad);
    }
});

// Formulario de jugador
formJugador.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(formJugador);
    const data = Object.fromEntries(formData);
    
    // Calcular edad automáticamente si hay fecha de nacimiento
    if (data.fecha_nacimiento) {
        data.edad = calcularEdad(data.fecha_nacimiento);
    }
    
    try {
        const url = '../api/jugadorAPI.php';
        const method = jugadorEditando ? 'PUT' : 'POST';
        
        if (jugadorEditando) {
            data.id_jugador = jugadorEditando;
        }
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            mostrarNotificacion(jugadorEditando ? 'Jugador actualizado correctamente' : 'Jugador registrado correctamente');
            cerrarModal();
            // Recargar con los filtros actuales después de guardar
            aplicarFiltros();
        } else {
            mostrarNotificacion(result.message || 'Error al guardar jugador', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion('Error al guardar jugador', 'error');
    }
});

// ============================
// INICIALIZACIÓN
// ============================
document.addEventListener('DOMContentLoaded', () => {
    obtenerJugadores(); // Cargar todos los jugadores al inicio
    obtenerCategorias();
});