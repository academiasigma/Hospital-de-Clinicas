
window.authPromise = (async () => {
    try {
        const res = await fetch('../../api/sesion.php');
        const data = await res.json();

        if (!res.ok || !data.autenticado) {
            window.location.href = '../index.html';
            return null;
        }

        // Guardar en el objeto global
        window.currentUser = data.usuario;

        // Intentar actualizar elementos comunes del header si existen en el DOM
        const txtNombre = document.querySelector('#txtNombre');
        const txtRol = document.querySelector('#txtRol');
        const btnCerrarSesion = document.querySelector('#btnCerrarSesion');

        if (txtNombre) {
            if (txtRol) {
                txtNombre.textContent = data.usuario.nombre;
                txtRol.textContent = data.usuario.rol;
            } else {
                txtNombre.textContent = `${data.usuario.nombre} (${data.usuario.rol})`;
            }
        }

        if (btnCerrarSesion) {
            btnCerrarSesion.addEventListener('click', async () => {
                try {
                    await fetch('../../api/logout.php', { method: 'POST' });
                    window.location.href = '../index.html';
                } catch (err) {
                    alert('Error al cerrar sesión.');
                }
            });
        }

        return data.usuario;
    } catch (error) {
        console.error('Error de autenticación:', error);
        window.location.href = '../index.html';
        return null;
    }
})();
