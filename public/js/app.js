// Sego Sambelan - App JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // ── Hamburger Menu Toggle ──────────────────
    const hamburger = document.getElementById('hamburger');
    const navMenu = document.querySelector('.navbar-menu');
    if (hamburger && navMenu) {
        hamburger.addEventListener('click', () => {
            navMenu.classList.toggle('open');
            hamburger.classList.toggle('active');
        });
    }

    // ── Admin Sidebar Toggle ───────────────────
    const adminHamburger = document.getElementById('admin-hamburger');
    const sidebar = document.getElementById('sidebar');
    if (adminHamburger && sidebar) {
        adminHamburger.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });
    }

    // ── Auto-dismiss flash alerts ──────────────
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});
