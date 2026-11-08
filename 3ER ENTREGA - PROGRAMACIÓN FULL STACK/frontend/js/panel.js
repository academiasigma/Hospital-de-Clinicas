document.addEventListener('DOMContentLoaded', async () => {
    const user = await window.authPromise;
    if (!user) return;

    const cardUsuarios = document.querySelector('#cardUsuarios');

    // Muestra el módulo de gestión de usuarios únicamente al Administrador (SUPERADMIN_IT)
    if (user.rol === 'SUPERADMIN_IT') {
        if (cardUsuarios) {
            cardUsuarios.style.display = 'flex';
        }
    }
});
