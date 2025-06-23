// ============================
// VARIABLES GLOBALES
// ============================
let entrenadores = [];
let entrenadorEditando = null;

// ============================
// ELEMENTOS DEL DOM
// ============================
const menuToggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');
const profileToggle = document.getElementById('profileToggle');
const profileDropdown = document.getElementById('profileDropdown');

// Elementos de la tabla y modales
const tablaEntrenadores = document.getElementById('tablaEntrenadores');
const totalEntrenadores = document.getElementById('totalEntrenadores');
const searchInput = document.getElementById('searchInput');
const filterEspecialidad = document.getElementById('filterEspecialidad');

// Modales y formularios
const modalEntrenador = document.getElementById('modalEntrenador');
const modalCategoria = document.getElementById('modalCategoria');
const formEntrenador = document.getElementById('formEntrenador');
const formCategoria = document.getElementById('formCategoria');
const modalTitle = document.getElementById('modalTitle');

// Botones
const btnNuevoEntrenador = document.getElementById('btnNuevoEntrenador');
const btnCancelar = document.getElementById('btnCancelar');
const btnCancelarCategoria = document.getElementById('btnCancelarCategoria');

// ============================
// FUNCIONES DEL MENÚ
// ============================
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

// Función para obtener entrenadores
async function obtenerEntrenadores() {
    try {
        const response = await fetch('../api/entrenadorAPI.php');
        if (!response.ok) throw new Error('Error en la respuesta del servidor');
        
        const data = await response.json();
        entrenadores = data;
        renderizarTabla(entrenadores);
        actualizarContador();
    } catch (error) {
        console.error('Error al cargar entrenadores:', error);
        mostrarNotificacion('Error al cargar los entrenadores', 'error');
    }
}

// Función para obtener las categorías disponibles
async function obtenerCategorias() {
    try {
        const response = await fetch('../api/categoriaAPI.php'); // Necesitarás crear este archivo
        if (!response.ok) throw new Error('Error en la respuesta del servidor');
        
        const categorias = await response.json();
        
        // Llenar el select de categorías
        const selectCategoria = document.getElementById('categoria');
        selectCategoria.innerHTML = '<option value="">Seleccionar categoría...</option>';
        
        categorias.forEach(categoria => {
            const option = document.createElement('option');
            option.value = categoria.id_categoria;
            option.textContent = `${categoria.nombre} (${categoria.edad_minima}-${categoria.edad_maxima} años)`;
            
            // Si ya tiene entrenador asignado, mostrar información
            if (categoria.id_entrenador) {
                option.textContent += ' - Ya asignada';
                option.disabled = true;
            }
            
            selectCategoria.appendChild(option);
        });
        
    } catch (error) {
        console.error('Error al cargar categorías:', error);
        mostrarNotificacion('Error al cargar las categorías', 'error');
    }
}

// Función para renderizar la tabla
function renderizarTabla(datos) {
    tablaEntrenadores.innerHTML = '';
    
    if (datos.length === 0) {
        tablaEntrenadores.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                    <div class="flex flex-col items-center">
                        <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                        <p class="text-lg font-medium">No hay entrenadores registrados</p>
                        <p class="text-sm">Agrega el primer entrenador para comenzar</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }

    datos.forEach(entrenador => {
        const fila = document.createElement('tr');
        fila.className = 'hover:bg-gray-50 transition-colors';
        
        fila.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                        <div class="h-10 w-10 rounded-full bg-gradient-to-r from-green-400 to-blue-500 flex items-center justify-center">
                            <span class="text-sm font-medium text-white">${entrenador.nombre.charAt(0)}${entrenador.apellido.charAt(0)}</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">${entrenador.nombre} ${entrenador.apellido}</div>
                        <div class="text-sm text-gray-500">ID: ${entrenador.cedula}</div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">
                    ${entrenador.telefono ? `<div class="flex items-center mb-1"><svg class="w-4 h-4 mr-1 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"></path></svg>${entrenador.telefono}</div>` : ''}
                    ${entrenador.correo ? `<div class="flex items-center"><svg class="w-4 h-4 mr-1 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path></svg>${entrenador.correo}</div>` : ''}
                </div>
            </td>
           <td class="px-6 py-4 whitespace-nowrap">
    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
        entrenador.especialidad === 'Futbol Profesional' ? 'bg-green-100 text-green-800' :
        entrenador.especialidad === 'Futbol Semi Profesional' ? 'bg-yellow-100 text-yellow-800' :
        entrenador.especialidad === 'Futbol Amateur' ? 'bg-blue-100 text-blue-800' :
        'bg-gray-100 text-gray-800'
    }">
        ${entrenador.especialidad || 'No especificada'}
    </span>
</td>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-1 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    ${entrenador.experiencia_años || 0} años
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${entrenador.fecha_contratacion ? new Date(entrenador.fecha_contratacion).toLocaleDateString('es-ES') : 'No especificada'}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                    ${entrenador.categoria || 'Sin asignar'}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                    <button onclick="editarEntrenador('${entrenador.id || entrenador.cedula}')" 
                            class="text-indigo-600 hover:text-indigo-900 transition-colors duration-200 px-3 py-1 rounded-md hover:bg-indigo-50">
                        <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                        </svg>
                        Editar
                    </button>
                    <button onclick="asignarCategoria('${entrenador.id || entrenador.cedula}', '${entrenador.nombre} ${entrenador.apellido}')" 
                            class="text-green-600 hover:text-green-900 transition-colors duration-200 px-3 py-1 rounded-md hover:bg-green-50">
                        <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Asignar
                    </button>
                    <button onclick="eliminarEntrenador('${entrenador.id || entrenador.cedula}', '${entrenador.nombre} ${entrenador.apellido}')" 
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
        
        tablaEntrenadores.appendChild(fila);
    });
}

// Función para actualizar contador
function actualizarContador() {
    totalEntrenadores.textContent = entrenadores.length;
}

// Función para filtrar entrenadores
async function filtrarPorEspecialidad(especialidad) {
  try {
      let url = '../api/entrenadorAPI.php';
      
      if (especialidad && especialidad !== '') {
          url += '?filtro=especialidad&especialidad=' + encodeURIComponent(especialidad);
      }
      
      const response = await fetch(url);
      if (!response.ok) throw new Error('Error en la respuesta del servidor');
      
      const data = await response.json();
      entrenadores = data; // Actualizar la variable global
      renderizarTabla(entrenadores);
      actualizarContador();
  } catch (error) {
      console.error('Error al filtrar entrenadores:', error);
      mostrarNotificacion('Error al filtrar los entrenadores', 'error');
  }
}

// Función modificada para filtrar entrenadores
function filtrarEntrenadores() {
  const searchTerm = searchInput.value.toLowerCase();
  const especialidadFiltro = filterEspecialidad.value;
  
  // Si hay filtro de especialidad, hacer consulta al servidor
  if (especialidadFiltro && especialidadFiltro !== '') {
      filtrarPorEspecialidad(especialidadFiltro).then(() => {
          // Después de obtener los datos filtrados del servidor,
          // aplicar el filtro de búsqueda por nombre localmente
          if (searchTerm) {
              const entrenadoriesFiltrados = entrenadores.filter(entrenador => {
                  return (entrenador.nombre + ' ' + entrenador.apellido).toLowerCase().includes(searchTerm);
              });
              renderizarTabla(entrenadoriesFiltrados);
          }
      });
  } else {
      // Si no hay filtro de especialidad, cargar todos y filtrar localmente
      if (entrenadores.length === 0) {
          // Si no hay datos cargados, cargar todos primero
          obtenerEntrenadores().then(() => {
              aplicarFiltroLocal(searchTerm);
          });
      } else {
          aplicarFiltroLocal(searchTerm);
      }
  }
}

// Función para aplicar filtro local por nombre
function aplicarFiltroLocal(searchTerm) {
  if (searchTerm) {
      const entrenadoriesFiltrados = entrenadores.filter(entrenador => {
          return (entrenador.nombre + ' ' + entrenador.apellido).toLowerCase().includes(searchTerm);
      });
      renderizarTabla(entrenadoriesFiltrados);
  } else {
      renderizarTabla(entrenadores);
  }
}

// Función específica para el cambio del combo de especialidad
function cambioEspecialidad() {
  const especialidadFiltro = filterEspecialidad.value;
  
  if (especialidadFiltro === '') {
      // Si selecciona "Todas", cargar todos los entrenadores
      obtenerEntrenadores();
  } else {
      // Si selecciona una especialidad específica, filtrar
      filtrarPorEspecialidad(especialidadFiltro);
  }
  
  // Limpiar el campo de búsqueda cuando cambia la especialidad
  searchInput.value = '';
}

// Event listeners modificados
searchInput.addEventListener('input', filtrarEntrenadores);
filterEspecialidad.addEventListener('change', cambioEspecialidad); // Cambiar a la nueva función

















// Función para abrir modal de nuevo entrenador
function abrirModalNuevo() {
    entrenadorEditando = null;
    modalTitle.textContent = 'Nuevo Entrenador';
    formEntrenador.reset();
    modalEntrenador.classList.remove('hidden');
}

// Función para cerrar modal
function cerrarModal() {
    modalEntrenador.classList.add('hidden');
    formEntrenador.reset();
    entrenadorEditando = null;
}

// Función para cerrar modal de categoría
function cerrarModalCategoria() {
    modalCategoria.classList.add('hidden');
    formCategoria.reset();
}

// Función para editar entrenador
function editarEntrenador(id) {
    const entrenador = entrenadores.find(e => (e.id || e.cedula) == id);
    if (!entrenador) {
        mostrarNotificacion('Entrenador no encontrado', 'error');
        return;
    }
    
    entrenadorEditando = id;
    modalTitle.textContent = 'Editar Entrenador';
    
    // Llenar el formulario con los datos del entrenador
    document.getElementById('cedula').value = entrenador.cedula || '';
    document.getElementById('nombre').value = entrenador.nombre || '';
    document.getElementById('apellido').value = entrenador.apellido || '';
    document.getElementById('telefono').value = entrenador.telefono || '';
    document.getElementById('correo').value = entrenador.correo || '';
    document.getElementById('especialidad').value = entrenador.especialidad || '';
    document.getElementById('experiencia_años').value = entrenador.experiencia_años || '';
    document.getElementById('fecha_contratacion').value = entrenador.fecha_contratacion || '';
    
    modalEntrenador.classList.remove('hidden');
}

// Función para eliminar entrenador
function eliminarEntrenador(id, nombre) {
    if (confirm(`¿Estás seguro de que deseas eliminar al entrenador ${nombre}?`)) {
        // Aquí harías la petición DELETE al servidor
        fetch(`../api/entrenadorAPI.php?id=${id}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarNotificacion('Entrenador eliminado correctamente');
                obtenerEntrenadores();
            } else {
                mostrarNotificacion('Error al eliminar entrenador', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarNotificacion('Error al eliminar entrenador', 'error');
        });
    }
}

// Función para asignar categoría (MODIFICADA)
function asignarCategoria(id, nombre) {
    document.getElementById('entrenadorSeleccionado').textContent = nombre;
    formCategoria.dataset.entrenadorId = id;
    
    // Cargar las categorías disponibles
    obtenerCategorias();
    
    modalCategoria.classList.remove('hidden');
}

// ============================
// EVENT LISTENERS
// ============================

// Búsqueda y filtros
searchInput.addEventListener('input', filtrarEntrenadores);
filterEspecialidad.addEventListener('change', filtrarEntrenadores);

// Botones principales
btnNuevoEntrenador.addEventListener('click', abrirModalNuevo);
btnCancelar.addEventListener('click', cerrarModal);
btnCancelarCategoria.addEventListener('click', cerrarModalCategoria);

// Cerrar modales al hacer clic fuera
modalEntrenador.addEventListener('click', (e) => {
    if (e.target === modalEntrenador) cerrarModal();
});

modalCategoria.addEventListener('click', (e) => {
    if (e.target === modalCategoria) cerrarModalCategoria();
});

// Formulario de entrenador
formEntrenador.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(formEntrenador);
    const data = Object.fromEntries(formData);
    
    try {
        const url = '../api/entrenadorAPI.php';
        const method = entrenadorEditando ? 'PUT' : 'POST';
        
        if (entrenadorEditando) {
            data.id = entrenadorEditando;
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
            mostrarNotificacion(entrenadorEditando ? 'Entrenador actualizado correctamente' : 'Entrenador creado correctamente');
            cerrarModal();
            obtenerEntrenadores();
        } else {
            mostrarNotificacion(result.message || 'Error al guardar entrenador', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion('Error al guardar entrenador', 'error');
    }
});

// Formulario de categoría
formCategoria.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const categoria = document.getElementById('categoria').value;
    const entrenadorId = formCategoria.dataset.entrenadorId;
    
    try {
        const response = await fetch('../api/entrenadorAPI.php', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: entrenadorId,
                categoria: categoria
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            mostrarNotificacion('Categoría asignada correctamente');
            cerrarModalCategoria();
            obtenerEntrenadores();
        } else {
            mostrarNotificacion('Error al asignar categoría', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion('Error al asignar categoría', 'error');
    }
});

// ============================
// INICIALIZACIÓN (MODIFICADA)
// ============================
document.addEventListener('DOMContentLoaded', () => {
    obtenerEntrenadores();
    obtenerCategorias(); // Línea agregada
});