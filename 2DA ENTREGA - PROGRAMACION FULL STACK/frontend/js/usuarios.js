document.addEventListener('DOMContentLoaded', () => {
    // Selectores del DOM
    const txtNombre = document.querySelector('#txtNombre');
    const txtRol = document.querySelector('#txtRol');
    const btnAbrirModalAlta = document.querySelector('#btnAbrirModalAlta');
    const inputBuscar = document.querySelector('#inputBuscar');
    const tablaCuerpo = document.querySelector('#tablaCuerpo');
    const alerta = document.querySelector('#alerta');

    // Selectores del modal Alta
    const modalAlta = document.querySelector('#modalAlta');
    const btnCerrarModalAlta = document.querySelector('#btnCerrarModalAlta');
    const btnCancelarModalAlta = document.querySelector('#btnCancelarModalAlta');
    const formAltaUsuario = document.querySelector('#formAltaUsuario');
    const btnGuardarUsuario = document.querySelector('#btnGuardarUsuario');

    // Selectores del modal Editar
    const modalEditar = document.querySelector('#modalEditar');
    const btnCerrarModalEditar = document.querySelector('#btnCerrarModalEditar');
    const btnCancelarModalEditar = document.querySelector('#btnCancelarModalEditar');
    const formEditarUsuario = document.querySelector('#formEditarUsuario');
    const btnActualizarUsuario = document.querySelector('#btnActualizarUsuario');

    // Campos modal Editar
    const editIdUsuario = document.querySelector('#edit_id_usuario');
    const editNombre = document.querySelector('#edit_nombre');
    const editEmail = document.querySelector('#edit_email');
    const editContrasenia = document.querySelector('#edit_contrasenia');
    const editRol = document.querySelector('#edit_rol');
    const editEstado = document.querySelector('#edit_estado');

    // Estado local
    let usuariosList = [];
    let currentUser = null;

    // Utilidad: Mostrar Alertas
    const mostrarAlerta = (mensaje, tipo = 'exito') => {
        alerta.textContent = mensaje;
        alerta.className = `alerta alerta-${tipo}`;
        alerta.classList.remove('oculta');
        setTimeout(() => alerta.classList.add('oculta'), 5000);
    };

    // Inicializar verificación de sesión usando authPromise
    window.authPromise.then(async (user) => {
        if (!user) return;
        currentUser = user;

        // Solo SUPERADMIN_IT puede ver el botón de registrar funcionarios
        if (currentUser.rol === 'SUPERADMIN_IT') {
            if (btnAbrirModalAlta) {
                btnAbrirModalAlta.style.display = 'inline-block';
            }
        }

        await cargarUsuarios();
    });

    // Cargar listado de usuarios de la API
    const cargarUsuarios = async () => {
        try {
            const res = await fetch('../../api/usuarios/index.php');
            const data = await res.json();

            if (!res.ok) {
                throw new Error(data.mensaje || 'Error al recuperar personal.');
            }

            usuariosList = data.usuarios;
            renderizarUsuarios();

        } catch (error) {
            tablaCuerpo.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-danger">
                        ✕ Error al cargar: ${error.message}
                    </td>
                </tr>
            `;
        }
    };

    // Renderizar filas en la tabla con el buscador en el cliente 
    const renderizarUsuarios = () => {
        const busqueda = inputBuscar.value.toLowerCase().trim();

        const usuariosFiltrados = usuariosList.filter(user => {
            return user.nombre.toLowerCase().includes(busqueda) || 
                   user.email.toLowerCase().includes(busqueda) ||
                   user.rol.toLowerCase().includes(busqueda);
        });

        tablaCuerpo.innerHTML = '';

        if (usuariosFiltrados.length === 0) {
            tablaCuerpo.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-muted">
                        No se encontraron funcionarios registrados.
                    </td>
                </tr>
            `;
            return;
        }

        usuariosFiltrados.forEach(user => {
            const tr = document.createElement('tr');
            
            const badgeStatus = user.estado === 'Activo' 
                ? '<span class="badge-status-activo">Activo</span>' 
                : '<span class="badge-status-inactivo">Inactivo</span>';

            // Mostrar botones de Editar y Eliminar solo para administradores
            let btnAcciones = '';
            if (currentUser && currentUser.rol === 'SUPERADMIN_IT') {
                const btnEdit = `<button class="btn-action btn-edit-usuario" data-id="${user.id_usuario}">✏️ Editar</button>`;
                
                let btnDel = '';
                if (currentUser.id_usuario === user.id_usuario) {
                    btnDel = `<span class="text-muted" style="font-size: 0.8rem; margin-left: 0.5rem;">(Tu Cuenta)</span>`;
                } else {
                    btnDel = `
                        <button class="btn-action btn-delete btn-eliminar-usuario" data-id="${user.id_usuario}" data-nombre="${user.nombre}">
                            🗑️ Eliminar
                        </button>
                    `;
                }
                btnAcciones = `<div style="display: flex; gap: 0.5rem; align-items: center;">${btnEdit} ${btnDel}</div>`;
            } else {
                btnAcciones = `<span class="text-muted" style="font-size: 0.8rem;">Sin Permisos</span>`;
            }

            tr.innerHTML = `
                <td><strong>#${user.id_usuario}</strong></td>
                <td>${user.nombre}</td>
                <td><a href="mailto:${user.email}">${user.email}</a></td>
                <td><span class="badge-rol" style="background-color: #64748b; font-size: 0.75rem;">${user.rol}</span></td>
                <td>${badgeStatus}</td>
                <td>${btnAcciones}</td>
            `;

            tablaCuerpo.appendChild(tr);
        });
    };

    // 4. Modal de Alta: Abrir y Cerrar
    btnAbrirModalAlta.addEventListener('click', () => {
        formAltaUsuario.reset();
        modalAlta.classList.remove('oculta');
    });

    const cerrarModalAlta = () => {
        modalAlta.classList.add('oculta');
        formAltaUsuario.reset();
    };

    btnCerrarModalAlta.addEventListener('click', cerrarModalAlta);
    btnCancelarModalAlta.addEventListener('click', cerrarModalAlta);

    // 5. Submit del formulario de alta (POST asíncrono)
    formAltaUsuario.addEventListener('submit', async (e) => {
        e.preventDefault();

        const nombre = document.querySelector('#nombre').value.trim();
        const email = document.querySelector('#email').value.trim();
        const contrasenia = document.querySelector('#contrasenia').value;
        const rol = document.querySelector('#rol').value;
        const estado = document.querySelector('#estado').value;

        if (!nombre || !email || !contrasenia || !rol || !estado) {
            alert('Por favor complete todos los campos obligatorios (*).');
            return;
        }

        if (contrasenia.length < 6) {
            alert('La contraseña temporal debe tener al menos 6 caracteres.');
            return;
        }

        btnGuardarUsuario.disabled = true;
        btnGuardarUsuario.textContent = 'Guardando...';

        try {
            const res = await fetch('../../api/usuarios/index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    nombre,
                    email,
                    contrasenia,
                    rol,
                    estado
                })
            });

            const data = await res.json();

            if (!res.ok) {
                throw new Error(data.mensaje || 'Error al procesar la solicitud.');
            }

            mostrarAlerta(data.mensaje, 'exito');
            cerrarModalAlta();
            await cargarUsuarios(); // Recargar grilla

        } catch (error) {
            alert('✕ Error: ' + error.message);
        } finally {
            btnGuardarUsuario.disabled = false;
            btnGuardarUsuario.textContent = 'Crear Funcionario';
        }
    });

    // Modal de Edición: Abrir, Rellenar y Cerrar
    const cerrarModalEditar = () => {
        modalEditar.classList.add('oculta');
        formEditarUsuario.reset();
    };

    btnCerrarModalEditar.addEventListener('click', cerrarModalEditar);
    btnCancelarModalEditar.addEventListener('click', cerrarModalEditar);

    // Delegación de clicks (Editar y Eliminar)
    tablaCuerpo.addEventListener('click', async (e) => {
        // Click en Editar
        if (e.target.classList.contains('btn-edit-usuario')) {
            const id = parseInt(e.target.dataset.id, 10);
            const user = usuariosList.find(u => u.id_usuario === id);

            if (user) {
                editIdUsuario.value = user.id_usuario;
                editNombre.value = user.nombre;
                editEmail.value = user.email;
                editContrasenia.value = ''; //en blanco por defecto
                editRol.value = user.rol;
                editEstado.value = user.estado;
                
                modalEditar.classList.remove('oculta');
            }
        }

        // Click en Eliminar
        if (e.target.classList.contains('btn-eliminar-usuario')) {
            const id = e.target.dataset.id;
            const nombre = e.target.dataset.nombre;

            if (confirm(`¿Está seguro que desea eliminar definitivamente al funcionario "${nombre}"?\nEsta acción no se puede deshacer.`)) {
                try {
                    const res = await fetch('../../api/usuarios/eliminar.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id_usuario: parseInt(id, 10) })
                    });

                    const data = await res.json();

                    if (!res.ok) {
                        throw new Error(data.mensaje || 'Error al intentar eliminar.');
                    }

                    mostrarAlerta(data.mensaje, 'exito');
                    await cargarUsuarios();

                } catch (error) {
                    alert('✕ Error: ' + error.message);
                }
            }
        }
    });

    // Submit del formulario de Edición (POST asíncrono)
    formEditarUsuario.addEventListener('submit', async (e) => {
        e.preventDefault();

        const id_usuario = parseInt(editIdUsuario.value, 10);
        const nombre = editNombre.value.trim();
        const email = editEmail.value.trim();
        const contrasenia = editContrasenia.value;
        const rol = editRol.value;
        const estado = editEstado.value;

        if (!nombre || !email || !rol || !estado) {
            alert('Por favor complete todos los campos obligatorios (*).');
            return;
        }

        if (contrasenia && contrasenia.length < 6) {
            alert('La nueva contraseña debe tener al menos 6 caracteres.');
            return;
        }

        btnActualizarUsuario.disabled = true;
        btnActualizarUsuario.textContent = 'Actualizando...';

        try {
            const res = await fetch('../../api/usuarios/actualizar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id_usuario,
                    nombre,
                    email,
                    contrasenia,
                    rol,
                    estado
                })
            });

            const data = await res.json();

            if (!res.ok) {
                throw new Error(data.mensaje || 'Error al procesar la actualización.');
            }

            mostrarAlerta(data.mensaje, 'exito');
            cerrarModalEditar();
            await cargarUsuarios(); //Recargar grilla

        } catch (error) {
            alert('✕ Error: ' + error.message);
        } finally {
            btnActualizarUsuario.disabled = false;
            btnActualizarUsuario.textContent = 'Guardar Cambios';
        }
    });

    //Buscador interactivo
    inputBuscar.addEventListener('input', renderizarUsuarios);
});
