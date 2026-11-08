document.addEventListener('DOMContentLoaded', () => {
    const formulario = document.querySelector('#loginForm');
    const inputEmail = document.querySelector('#email');
    const inputPassword = document.querySelector('#password');
    const btnIngresar = document.querySelector('#btnIngresar');
    const cajaAlerta = document.querySelector('#alerta');

    const mostrarAlerta = (mensaje, tipo = 'error') => {
        cajaAlerta.textContent = mensaje;
        cajaAlerta.classList.remove('oculta', 'error', 'exito');
        cajaAlerta.classList.add(tipo);
    };

    const limpiarAlerta = () => {
        cajaAlerta.textContent = '';
        cajaAlerta.classList.add('oculta');
    };

    formulario.addEventListener('submit', async (evento) => {
        evento.preventDefault();
        limpiarAlerta();

        const email = inputEmail.value.trim();
        const password = inputPassword.value.trim();

        if (!email || !password) {
            mostrarAlerta('Por favor ingrese su correo y contraseña.', 'error');
            return;
        }

        btnIngresar.disabled = true;
        btnIngresar.textContent = 'Verificando...';

        try {
            const respuesta = await fetch('../api/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email, password })
            });

            const datos = await respuesta.json();

            if (!respuesta.ok) {
                throw new Error(datos.mensaje || 'Error al autenticar credenciales.');
            }

            mostrarAlerta('Acceso correcto. Redirigiendo...', 'exito');

            setTimeout(() => {
                window.location.href = 'html/panel.html';
            }, 800);

        } catch (error) {
            mostrarAlerta(error.message, 'error');
        } finally {
            btnIngresar.disabled = false;
            btnIngresar.textContent = 'Ingresar como Funcionario';
        }
    });
});
