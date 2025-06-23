// usuarioFrm.js - JavaScript actualizado para manejar la base de datos

// Elementos del DOM
const menuToggle = document.getElementById('menuToggle'); 
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');
const profileToggle = document.getElementById('profileToggle');
const profileDropdown = document.getElementById('profileDropdown');
const modalUsuario = document.getElementById('modalUsuario');
const btnNuevoUsuario = document.getElementById('btnNuevoUsuario');
const cerrarModal = document.getElementById('cerrarModal');
const cancelarUsuario = document.getElementById('cancelarUsuario');
const formUsuario = document.getElementById('formUsuario');
const tituloModal = document.getElementById('tituloModal');
const tablaUsuarios = document.getElementById('tablaUsuarios');

// Variables globales
let usuarioEditando = null;
const API_URL = 'http://localhost/Proyecto_CDMomil/api/usuarioAPI.php';

// Funciones de navegación y UI
function toggleMenu() {
    sidebar.classList.toggle('open');
    overlay.classList.toggle('open');
}

function toggleProfile() {
    profileDropdown.classList.toggle('open');
}

function abrirModal(usuario = null) {
    usuarioEditando = usuario;
    
    if (usuario) {
        // Modo edición
        tituloModal.textContent = 'Editar Usuario';
        document.getElementById('nombre').value = usuario.nombre;
        document.getElementById('apellido').value = usuario.apellido;
        document.getElementById('usuario').value = usuario.usuario;
        document.getElementById('contrasena').value = '';
        document.getElementById('contrasena').placeholder = 'Dejar vacío para mantener la contraseña actual';
        document.getElementById('contrasena').required = false;
    } else {
        // Modo creación
        tituloModal.textContent = 'Nuevo Usuario';
        formUsuario.reset();
        document.getElementById('contrasena').placeholder = '';
        document.getElementById('contrasena').required = true;
    }
    
    modalUsuario.classList.add('open');
}

function cerrarModalUsuario() {
    modalUsuario.classList.remove('open');
    formUsuario.reset();
    usuarioEditando = null;
}

function navigateTo(section) {
    console.log('Navegando a:', section);
    if (section !== 'usuarios') {
        alert('Funcionalidad de ' + section + ' en desarrollo');
    }
}

// Funciones de API
async function cargarUsuarios() {
    try {
        mostrarCargando(true);
        const response = await fetch(API_URL);
        const data = await response.json();
        
        if (data.success) {
            mostrarUsuarios(data.data);
        } else {
            mostrarError('Error al cargar usuarios: ' + data.message);
        }
    } catch (error) {
        mostrarError('Error de conexión: ' + error.message);
    } finally {
        mostrarCargando(false);
    }
}

async function guardarUsuario(datosUsuario) {
    try {
        const url = usuarioEditando ? `${API_URL}?id=${usuarioEditando.id_admin}` : API_URL;
        const method = usuarioEditando ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(datosUsuario)
        });
        
        const data = await response.json();
        
        if (data.success) {
            mostrarExito(data.message);
            cerrarModalUsuario();
            cargarUsuarios();
        } else {
            mostrarError(data.message);
        }
    } catch (error) {
        mostrarError('Error de conexión: ' + error.message);
    }
}

async function eliminarUsuario(id) {
    if (!confirm('¿Estás seguro de que quieres eliminar este usuario?')) {
        return;
    }
    
    try {
        const response = await fetch(`${API_URL}?id=${id}`, {
            method: 'DELETE'
        });
        
        const data = await response.json();
        
        if (data.success) {
            mostrarExito(data.message);
            cargarUsuarios();
        } else {
            mostrarError(data.message);
        }
    } catch (error) {
        mostrarError('Error de conexión: ' + error.message);
    }
}

// Funciones de UI
function mostrarUsuarios(usuarios) {
    tablaUsuarios.innerHTML = '';
    
    if (usuarios.length === 0) {
        tablaUsuarios.innerHTML = `
            <tr>
                <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                    No hay usuarios registrados
                </td>
            </tr>
        `;
        return;
    }
    
    usuarios.forEach(usuario => {
        const fila = document.createElement('tr');
        fila.className = 'hover:bg-gray-50';
        fila.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">${usuario.id_admin}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">${usuario.nombre}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">${usuario.apellido}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">${usuario.usuario}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium border-b">
                <button onclick="abrirModal(${JSON.stringify(usuario).replace(/"/g, '&quot;')})" 
                        class="text-blue-600 hover:text-blue-900 mr-3">
                    Editar
                </button>
                <button onclick="eliminarUsuario(${usuario.id_admin})" 
                        class="text-red-600 hover:text-red-900">
                    Eliminar
                </button>
            </td>
        `;
        tablaUsuarios.appendChild(fila);
    });
}

function mostrarCargando(mostrar) {
    if (mostrar) {
        tablaUsuarios.innerHTML = `
            <tr>
                <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                    <div class="flex items-center justify-center">
                        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-green-600"></div>
                        <span class="ml-2">Cargando...</span>
                    </div>
                </td>
            </tr>
        `;
    }
}

function mostrarError(mensaje) {
    // Crear notificación de error
    const notificacion = document.createElement('div');
    notificacion.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
    notificacion.innerHTML = `
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
            ${mensaje}
        </div>
    `;
    document.body.appendChild(notificacion);
    
    setTimeout(() => {
        notificacion.remove();
    }, 5000);
}

function mostrarExito(mensaje) {
    // Crear notificación de éxito
    const notificacion = document.createElement('div');
    notificacion.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
    notificacion.innerHTML = `
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            ${mensaje}
        </div>
    `;
    document.body.appendChild(notificacion);
    
    setTimeout(() => {
        notificacion.remove();
    }, 3000);
}

// Event listeners
menuToggle.addEventListener('click', toggleMenu);
overlay.addEventListener('click', toggleMenu);
profileToggle.addEventListener('click', toggleProfile);
btnNuevoUsuario.addEventListener('click', () => abrirModal());
cerrarModal.addEventListener('click', cerrarModalUsuario);
cancelarUsuario.addEventListener('click', cerrarModalUsuario);

// Cerrar dropdown del perfil al hacer clic fuera
document.addEventListener('click', (e) => {
    if (!profileToggle.contains(e.target) && !profileDropdown.contains(e.target)) {
        profileDropdown.classList.remove('open');
    }
});

// Cerrar modal al hacer clic fuera
modalUsuario.addEventListener('click', (e) => {
    if (e.target === modalUsuario) {
        cerrarModalUsuario();
    }
});

// Manejar envío del formulario
formUsuario.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(formUsuario);
    const datosUsuario = {
        nombre: formData.get('nombre').trim(),
        apellido: formData.get('apellido').trim(),
        usuario: formData.get('usuario').trim(),
        contrasena: formData.get('contrasena').trim()
    };
    
    // Validaciones del cliente
    if (!datosUsuario.nombre || !datosUsuario.apellido || !datosUsuario.usuario) {
        mostrarError('Todos los campos son obligatorios');
        return;
    }
    
    if (!usuarioEditando && !datosUsuario.contrasena) {
        mostrarError('La contraseña es obligatoria para nuevos usuarios');
        return;
    }
    
    if (datosUsuario.contrasena && datosUsuario.contrasena.length < 6) {
        mostrarError('La contraseña debe tener al menos 6 caracteres');
        return;
    }
    
    // Si estamos editando y no se proporcionó contraseña, no la enviamos
    if (usuarioEditando && !datosUsuario.contrasena) {
        delete datosUsuario.contrasena;
    }
    
    await guardarUsuario(datosUsuario);
});

// Cerrar menú al hacer clic en una opción
const menuItems = sidebar.querySelectorAll('.cursor-pointer');
menuItems.forEach(item => {
    item.addEventListener('click', () => {
        sidebar.classList.remove('open');
        overlay.classList.remove('open');
    });
});

// Cargar usuarios al cargar la página
document.addEventListener('DOMContentLoaded', () => {
    cargarUsuarios();
});