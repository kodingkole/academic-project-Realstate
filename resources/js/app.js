const mobileMenuButton = document.getElementById('mobileMenuButton');
const navLinks = document.getElementById('navLinks');

if (mobileMenuButton && navLinks) {
    mobileMenuButton.addEventListener('click', () => {
        navLinks.classList.toggle('open');
    });
}

const togglePassword = document.getElementById('togglePassword');
const passwordInput = document.getElementById('password');

if (togglePassword && passwordInput) {
    togglePassword.addEventListener('click', () => {
        const isPassword = passwordInput.type === 'password';

        passwordInput.type = isPassword ? 'text' : 'password';
        togglePassword.textContent = isPassword ? 'Hide' : 'Show';
    });
}

const sidebarToggle = document.getElementById('sidebarToggle');
const portalSidebar = document.getElementById('portalSidebar');

if (sidebarToggle && portalSidebar) {
    sidebarToggle.addEventListener('click', () => {
        portalSidebar.classList.toggle('open');
    });
}