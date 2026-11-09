/**
 * Terminal móvil del chofer.
 * Soporta dos modos de ingreso coherentes con el modelo de datos:
 *   1. Ingreso directo del Chofer desde el Login mediante su N° de Licencia de conducir (tabla `chofer`).
 *   2. Ingreso en modo Supervisión por parte del Gestor Integral / Superadmin TI desde Gestión de Traslados.
 * Permite registrar el avance en tiempo real: salida (En Curso), llegada a destino (Retornando) y retorno (Finalizado).
 */
document.addEventListener('DOMContentLoaded', async () => {
    const API = '../../api';
    const $ = (sel) => document.querySelector(sel);

    const selectChofer = $('#selectChoferActivo');
    const loginChoferBox = $('#loginChoferBox');
    const panelChoferIdentificado = $('#panelChoferIdentificado');
    const sinViajeBox = $('#sinViajeBox');
    const viajeAsignadoBox = $('#viajeAsignadoBox');

    let modoActual = 'anonimo'; // 'supervisor' | 'chofer' | 'anonimo'
    let idChoferActivo = null;
    let viajeActivo = null;
    let timerRefresco = null;

    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) =>
        ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    async function llamar(url, metodo = 'GET', cuerpo = null) {
        const opciones = { method: metodo, headers: {} };
        if (cuerpo !== null) {
            opciones.headers['Content-Type'] = 'application/json';
            opciones.body = JSON.stringify(cuerpo);
        }
        try {
            const res = await fetch(url, opciones);
            return { ok: res.ok, data: await res.json().catch(() => ({})) };
        } catch (e) {
            return { ok: false, data: { mensaje: 'No se pudo conectar con el servidor.' } };
        }
    }

    async function inicializarTerminal() {
        const r = await llamar(`${API}/choferes/index.php?accion=sesion`);
        if (!r.ok) return;

        modoActual = r.data.modo || 'anonimo';
        const choferes = r.data.choferes_demo || [];

        if (modoActual === 'supervisor') {
            loginChoferBox.classList.add('oculta');
            panelChoferIdentificado.classList.remove('oculta');
            $('#bloqueSelectorSupervisor').classList.remove('oculta');
            $('#bloqueDatosChoferDirecto').classList.add('oculta');
            $('#btnVolverTraslados').classList.remove('oculta');

            $('#txtNombre').textContent = `${r.data.usuario.nombre} (${r.data.usuario.rol})`;
            selectChofer.innerHTML = choferes.map((c) =>
                `<option value="${esc(c.id_chofer)}">${esc(c.nombre)} — Lic. ${esc(c.licencia)}</option>`
            ).join('');
            idChoferActivo = selectChofer.value;
            await buscarViajeAsignado();
            iniciarAutoRefresco();
            return;
        }

        if (modoActual === 'chofer' && r.data.chofer) {
            mostrarVistaChoferDirecto(r.data.chofer);
            await buscarViajeAsignado();
            iniciarAutoRefresco();
            return;
        }

        // Modo anónimo: mostrar formulario de ingreso por licencia de conducir
        detenerAutoRefresco();
        panelChoferIdentificado.classList.add('oculta');
        sinViajeBox.classList.add('oculta');
        viajeAsignadoBox.classList.add('oculta');
        loginChoferBox.classList.remove('oculta');

        const contenedorChips = $('#chipsChoferesDemo');
        if (choferes.length > 0) {
            contenedorChips.innerHTML = `<small style="width:100%; color:#64748b; font-size:0.75rem;">Choferes registrados (clic para completar):</small>` +
                choferes.map((c) =>
                    `<button type="button" class="btn-chip-licencia" data-licencia="${esc(c.licencia)}" style="background:#f1f5f9; border:1px solid #cbd5e1; border-radius:4px; padding:0.25rem 0.5rem; font-size:0.75rem; cursor:pointer; color:#334155;">
                        ${esc(c.nombre)} (<strong>${esc(c.licencia)}</strong>)
                    </button>`
                ).join('');
        }
    }

    function mostrarVistaChoferDirecto(chofer) {
        loginChoferBox.classList.add('oculta');
        panelChoferIdentificado.classList.remove('oculta');
        $('#bloqueSelectorSupervisor').classList.add('oculta');
        $('#bloqueDatosChoferDirecto').classList.remove('oculta');
        $('#btnVolverTraslados').classList.add('oculta');

        $('#txtChoferDirectoNombre').textContent = `🚑 ${chofer.nombre}`;
        $('#txtChoferDirectoLicencia').textContent = chofer.licencia;
        $('#txtNombre').textContent = 'Chofer en Turno';
        idChoferActivo = String(chofer.id_chofer);
    }

    function iniciarAutoRefresco() {
        detenerAutoRefresco();
        timerRefresco = setInterval(buscarViajeAsignado, 30000);
    }

    function detenerAutoRefresco() {
        if (timerRefresco) {
            clearInterval(timerRefresco);
            timerRefresco = null;
        }
    }

    $('#chipsChoferesDemo').addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-chip-licencia');
        if (!btn) return;
        $('#inputLicenciaChofer').value = btn.dataset.licencia;
        $('#msgLoginChofer').textContent = '';
    });

    $('#formLoginChofer').addEventListener('submit', async (e) => {
        e.preventDefault();
        $('#msgLoginChofer').textContent = '';
        const licencia = $('#inputLicenciaChofer').value.trim();
        if (!licencia) return;

        const r = await llamar(`${API}/choferes/index.php?accion=identificar`, 'POST', { licencia });
        if (!r.ok) {
            $('#msgLoginChofer').textContent = r.data.mensaje || 'Licencia no válida.';
            return;
        }
        modoActual = 'chofer';
        mostrarVistaChoferDirecto(r.data.chofer);
        await buscarViajeAsignado();
        iniciarAutoRefresco();
    });

    $('#btnCerrarSesion').addEventListener('click', async () => {
        detenerAutoRefresco();
        if (modoActual === 'supervisor') {
            await llamar(`${API}/logout.php`, 'POST');
            window.location.href = '../index.html';
        } else {
            await llamar(`${API}/choferes/index.php?accion=salir`, 'POST');
            $('#inputLicenciaChofer').value = '';
            await inicializarTerminal();
        }
    });

    async function buscarViajeAsignado() {
        $('#msgChofer').textContent = '';
        viajeActivo = null;
        const idConsulta = modoActual === 'supervisor' ? selectChofer.value : idChoferActivo;
        if (idConsulta) {
            const r = await llamar(`${API}/traslados/index.php?id_chofer=${encodeURIComponent(idConsulta)}`);
            const lista = r.ok ? (r.data.datos || []) : [];
            // Prioriza el viaje ya iniciado; si no hay, el próximo pendiente (el listado viene del más reciente al más antiguo).
            viajeActivo = lista.find((t) => t.estado === 'En Curso' || t.estado === 'Retornando')
                || lista.filter((t) => t.estado === 'Pendiente').pop()
                || null;
        }
        mostrarViaje();
    }

    function mostrarViaje() {
        const hay = viajeActivo !== null;
        sinViajeBox.classList.toggle('oculta', hay);
        viajeAsignadoBox.classList.toggle('oculta', !hay);
        if (!hay) return;

        const t = viajeActivo;
        $('#txtViajeId').textContent = `#${t.id_traslado}`;
        $('#txtViajeOrigen').textContent = `${t.sector_origen} (Piso ${t.piso_origen}${t.habitacion_origen ? ', hab. ' + t.habitacion_origen : ''})`;
        $('#txtViajeDestino').textContent = `${t.sector_destino} - ${t.direccion_destino}`;
        $('#txtViajePaciente').textContent = t.id_paciente ? t.paciente_nombre : `${t.objeto_nombre} (Objeto)`;
        $('#txtViajeHorario').textContent = `${t.fecha} ${t.hora_salida.substring(0, 5)} (llegada est. ${t.hora_estimada_llegada.substring(0, 5)})`;
        $('#txtViajeVehiculo').textContent = t.vehiculo_matricula ? `${t.tipo_vehiculo} ${t.vehiculo_matricula}` : 'Sin asignar';
        $('#txtViajeEnfermero').textContent = t.enfermero_nombre || 'No requiere';

        const badge = $('#badgePrioridadChofer');
        badge.textContent = t.prioridad.toUpperCase();
        badge.className = 'badge-prioridad ' + ({ Alta: 'badge-prio-alta', Media: 'badge-prio-media' }[t.prioridad] || 'badge-prio-baja');

        const estado = $('#txtViajeEstado');
        estado.textContent = t.estado;
        estado.className = 'badge-estado ' + (t.estado === 'Pendiente' ? 'status-yellow' : 'status-blue');

        $('#actionsPendiente').classList.toggle('oculta', t.estado !== 'Pendiente');
        $('#actionsEnCurso').classList.toggle('oculta', t.estado !== 'En Curso');
        $('#actionsRetornando').classList.toggle('oculta', t.estado !== 'Retornando');
    }

    async function cambiarEstado(nuevoEstado) {
        if (!viajeActivo) return;
        const r = await llamar(`${API}/traslados/index.php?id=${viajeActivo.id_traslado}&accion=estado`, 'PUT', { estado: nuevoEstado });
        if (!r.ok) {
            $('#msgChofer').textContent = (r.data.errores && r.data.errores.join(' ')) || r.data.mensaje || 'No se pudo actualizar el estado.';
            return;
        }
        if (nuevoEstado === 'Finalizado') alert('Traslado finalizado. Registro guardado en el historial.');
        await buscarViajeAsignado();
    }

    selectChofer.addEventListener('change', buscarViajeAsignado);
    $('#btnIniciarViaje').addEventListener('click', () => cambiarEstado('En Curso'));
    $('#btnRegistrarLlegada').addEventListener('click', () => cambiarEstado('Retornando'));
    $('#btnFinalizarViaje').addEventListener('click', () => cambiarEstado('Finalizado'));

    await inicializarTerminal();
});
