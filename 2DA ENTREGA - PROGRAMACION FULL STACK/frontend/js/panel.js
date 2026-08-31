document.addEventListener('DOMContentLoaded', async () => {
    const txtNombre = document.querySelector('#txtNombre');
    const txtRol = document.querySelector('#txtRol');
    const btnCerrarSesion = document.querySelector('#btnCerrarSesion');

    try {
        const res = await fetch('../../api/sesion.php', {
            method: 'GET'
        });

        const datos = await res.json();

        if (!res.ok || !datos.autenticado) {
            window.location.href = '../index.html';
            return;
        }

        txtNombre.textContent = datos.usuario.nombre;
        txtRol.textContent = datos.usuario.rol;

    } catch (error) {
        window.location.href = '../index.html';
    }

    btnCerrarSesion.addEventListener('click', async () => {
        try {
            await fetch('../../api/logout.php', {
                method: 'POST'
            });
            window.location.href = '../index.html';
        } catch (err) {
            alert('Error al cerrar sesión.');
        }
    });
});
