const menuToggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');
const profileToggle = document.getElementById('profileToggle');
const profileDropdown = document.getElementById('profileDropdown');

function toggleMenu() {
  sidebar.classList.toggle('open');
  overlay.classList.toggle('open');
}

function toggleProfile() {
  profileDropdown.classList.toggle('open');
}

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
