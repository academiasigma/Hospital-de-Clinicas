// logica para la terminal movil del chofer
document.addEventListener('DOMContentLoaded', async () => {
    const API = '../../api';
    const $ = (sel) => document.querySelector(sel);

    const usuario = await window.authPromise;
    if (!usuario) return;

    const selectChofer = $('#selectChoferActivo');
    let viajeActivo = null;

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

    async function cargarChoferes() {
        const r = await llamar(`${API}/choferes/index.php`);
        const choferes = r.ok ? r.data.datos : [];
        selectChofer.innerHTML = choferes.map((c) => `<option value="${esc(c.id_chofer)}">${esc(c.nombre)}</option>`).join('');
    }

    async function buscarViajeAsignado() {
        $('#msgChofer').textContent = '';
        viajeActivo = null;
        if (selectChofer.value) {
            const r = await llamar(`${API}/traslados/index.php?id_chofer=${encodeURIComponent(selectChofer.value)}`);
            const lista = r.ok ? r.data.datos : [];
            // muestra primero si tiene un viaje en curso y sino el proximo pendiente
            viajeActivo = lista.find((t) => t.estado === 'En Curso' || t.estado === 'Retornando')
                || lista.filter((t) => t.estado === 'Pendiente').pop()
                || null;
        }
        mostrarViaje();
    }

    function mostrarViaje() {
        const hay = viajeActivo !== null;
        $('#sinViajeBox').classList.toggle('oculta', hay);
        $('#viajeAsignadoBox').classList.toggle('oculta', !hay);
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

    await cargarChoferes();
    await buscarViajeAsignado();
    setInterval(buscarViajeAsignado, 30000);
});
