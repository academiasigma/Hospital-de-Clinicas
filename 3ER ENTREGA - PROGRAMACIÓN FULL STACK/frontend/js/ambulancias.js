// logica para el panel de traslados y ambulancias
document.addEventListener('DOMContentLoaded', async () => {
    const API = '../../api';
    const ROLES_TRANSPORTE = ['SUPERADMIN_IT', 'GESTOR_INTEGRAL'];

    const usuario = await window.authPromise;
    if (!usuario) return;

    const esTransporte = ROLES_TRANSPORTE.includes(usuario.rol);
    const esDivision = usuario.rol === 'USUARIO_DIVISION';
    const puedeCrear = esTransporte || esDivision;

    // selectores del dom
    const $ = (sel) => document.querySelector(sel);
    const form = $('#formAltaTraslado');
    const tabla = $('#tablaTrasladosCuerpo');
    const msgFormulario = $('#msgFormulario');
    const campos = [
        'fecha', 'hora_salida', 'hora_estimada_llegada', 'limite_horario',
        'sector_origen', 'piso_origen', 'habitacion_origen',
        'sector_destino', 'piso_destino', 'habitacion_destino', 'direccion_destino',
        'division_solicitante', 'motivo_procedimiento', 'responsable_recepcion',
        'prioridad', 'observaciones'
    ];

    let traslados = [];
    let catalogos = { vehiculos: [], choferes: [], enfermeros: [], objetos: [] };
    let idEnEdicion = null;

    // funciones auxiliares
    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) =>
        ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const hhmm = (h) => (h ? h.substring(0, 5) : '--:--');

    async function llamar(url, metodo = 'GET', cuerpo = null) {
        const opciones = { method: metodo, headers: {} };
        if (cuerpo !== null) {
            opciones.headers['Content-Type'] = 'application/json';
            opciones.body = JSON.stringify(cuerpo);
        }
        try {
            const res = await fetch(url, opciones);
            const data = await res.json().catch(() => ({}));
            if (res.status === 401) {
                window.location.href = '../index.html';
            }
            return { ok: res.ok, status: res.status, data };
        } catch (e) {
            return { ok: false, status: 0, data: { mensaje: 'No se pudo conectar con el servidor.' } };
        }
    }

    const mensajeDeError = (r) => {
        const lista = r.data.errores && r.data.errores.length ? r.data.errores : [r.data.mensaje || 'Error inesperado.'];
        return lista.join(' ');
    };

    // mostramos u ocultamos partes segun el rol del usuario
    if (!puedeCrear) $('#seccionFormulario').classList.add('oculta');
    if (esDivision) $('#grupoRecursos').classList.add('oculta');
    if (esTransporte) {
        $('#lnkRecursos').classList.remove('oculta');
        $('#lnkChofer').classList.remove('oculta');
    }
    if (esDivision) {
        $('#subtituloFormulario').textContent = 'Solicitud de traslado: el área de transporte asignará vehículo y personal.';
        $('#btnGuardar').textContent = 'Enviar Solicitud';
    }

    // carga de los selects con los datos de la base
    function llenarSelect(select, filas, textoVacio, etiqueta) {
        select.innerHTML = `<option value="">${esc(textoVacio)}</option>` +
            filas.map((f) => `<option value="${esc(f.id)}">${esc(etiqueta(f))}</option>`).join('');
    }

    async function cargarCatalogos() {
        const [v, c, e, o] = await Promise.all([
            llamar(`${API}/vehiculos/index.php`), llamar(`${API}/choferes/index.php`),
            llamar(`${API}/enfermeros/index.php`), llamar(`${API}/objetos/index.php`)
        ]);
        catalogos.vehiculos = v.data.datos || [];
        catalogos.choferes = c.data.datos || [];
        catalogos.enfermeros = e.data.datos || [];
        catalogos.objetos = o.data.datos || [];

        llenarSelect($('#vehiculo'), catalogos.vehiculos.map((x) => ({ ...x, id: x.id_vehiculo })), 'Sin asignar',
            (x) => `${x.tipo_vehiculo} ${x.matricula} (${x.estado})`);
        llenarSelect($('#filtroVehiculo'), catalogos.vehiculos.map((x) => ({ ...x, id: x.id_vehiculo })), 'Todos los vehículos',
            (x) => `${x.tipo_vehiculo} ${x.matricula}`);
        llenarSelect($('#chofer'), catalogos.choferes.map((x) => ({ ...x, id: x.id_chofer })), 'Sin asignar',
            (x) => x.nombre);
        llenarSelect($('#enfermero'), catalogos.enfermeros.map((x) => ({ ...x, id: x.id_enfermero })), 'Sin asignar / no requiere',
            (x) => x.nombre);
        llenarSelect($('#objeto'), catalogos.objetos.map((x) => ({ ...x, id: x.id_objeto })), 'Seleccione material',
            (x) => x.nombre);
    }

    // cambio entre traslado de paciente o de objeto
    function esOrgano() {
        const obj = catalogos.objetos.find((o) => String(o.id_objeto) === $('#objeto').value);
        return !!obj && obj.tipo_objeto === 'organo';
    }

    function actualizarTipo() {
        const esPaciente = $('#tipo_traslado').value === 'paciente';
        $('#grupo_paciente').classList.toggle('oculta', !esPaciente);
        $('#grupo_objeto').classList.toggle('oculta', esPaciente);
        $('#grupo_enfermero').classList.toggle('oculta', !esPaciente);
        if (!esPaciente) $('#enfermero').value = '';
        actualizarOrgano();
    }

    function actualizarOrgano() {
        const organo = $('#tipo_traslado').value === 'objeto' && esOrgano();
        $('#lblLimite').textContent = organo ? 'Hora límite * (órgano)' : 'Límite de horario';
        if (organo) $('#prioridad').value = 'Alta';
        $('#prioridad').disabled = organo;
    }

    $('#tipo_traslado').addEventListener('change', actualizarTipo);
    $('#objeto').addEventListener('change', actualizarOrgano);

    // si escribe una cedula que ya existe cargamos los datos del paciente
    $('#pac_documento').addEventListener('blur', async () => {
        const doc = $('#pac_documento').value.trim();
        if (!/^[0-9]{6,12}$/.test(doc)) return;
        const r = await llamar(`${API}/pacientes/index.php?documento=${encodeURIComponent(doc)}`);
        const p = r.ok && r.data.datos && r.data.datos[0];
        if (p) {
            cargarPaciente(p);
            $('#pacienteAviso').textContent = 'Paciente ya registrado: se cargaron sus datos (puede actualizarlos).';
        } else {
            $('#pacienteAviso').textContent = 'Paciente nuevo: complete sus datos para registrarlo.';
        }
    });

    function cargarPaciente(p) {
        $('#pac_documento').value = p.documento;
        $('#pac_nombre').value = p.nombre;
        $('#pac_nacimiento').value = p.fecha_de_nacimiento;
        $('#pac_telefono').value = p.telefono || '';
        $('#pac_medios').checked = !!Number(p.se_maneja_por_sus_medios);
        $('#pac_oxigeno').checked = !!Number(p.requiere_oxigeno);
        $('#pac_camilla').checked = !!Number(p.requiere_camilla);
        $('#pac_aislamiento').checked = !!Number(p.requiere_aislamiento);
    }

    // validacion de los campos del formulario
    function validarFormulario() {
        const errores = [];
        const v = (id) => $('#' + id).value.trim();
        const esPaciente = $('#tipo_traslado').value === 'paciente';

        ['fecha', 'hora_salida', 'hora_estimada_llegada', 'sector_origen', 'piso_origen', 'sector_destino',
            'piso_destino', 'direccion_destino', 'division_solicitante', 'motivo_procedimiento',
            'responsable_recepcion'].forEach((id) => {
            if (!v(id)) errores.push(`Complete el campo "${$('label[for="' + id + '"]').textContent.replace('*', '').trim()}".`);
        });

        if (esPaciente) {
            if (!/^[0-9]{6,12}$/.test(v('pac_documento'))) errores.push('La C.I. debe tener entre 6 y 12 dígitos, sin puntos ni guiones.');
            if (!v('pac_nombre')) errores.push('Ingrese el nombre del paciente.');
            if (!v('pac_nacimiento')) errores.push('Ingrese la fecha de nacimiento del paciente.');
            else if (v('pac_nacimiento') > new Date().toISOString().substring(0, 10)) errores.push('La fecha de nacimiento no puede ser futura.');
            if (v('pac_telefono') && !/^[0-9+\s-]{7,20}$/.test(v('pac_telefono'))) errores.push('El teléfono del paciente no es válido.');
        } else if (!$('#objeto').value) {
            errores.push('Seleccione el órgano o material a trasladar.');
        }

        if (v('hora_salida') && v('hora_estimada_llegada') && v('hora_estimada_llegada') <= v('hora_salida')) {
            errores.push('La llegada estimada debe ser posterior a la hora de salida.');
        }
        if (!esPaciente && esOrgano() && !v('limite_horario')) {
            errores.push('El traslado de un órgano exige la hora límite.');
        }
        if (esPaciente && !esDivision && $('#prioridad').value === 'Alta' && $('#vehiculo').value && !$('#enfermero').value) {
            errores.push('Un paciente con prioridad Alta requiere enfermero acompañante.');
        }
        return errores;
    }

    // guardar o actualizar un traslado
    async function guardarPaciente() {
        const r = await llamar(`${API}/pacientes/index.php`, 'POST', {
            documento: $('#pac_documento').value.trim(),
            nombre: $('#pac_nombre').value.trim(),
            telefono: $('#pac_telefono').value.trim(),
            fecha_de_nacimiento: $('#pac_nacimiento').value,
            se_maneja_por_sus_medios: $('#pac_medios').checked ? 1 : 0,
            requiere_oxigeno: $('#pac_oxigeno').checked ? 1 : 0,
            requiere_camilla: $('#pac_camilla').checked ? 1 : 0,
            requiere_aislamiento: $('#pac_aislamiento').checked ? 1 : 0
        });
        return r;
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        msgFormulario.textContent = '';

        const errores = validarFormulario();
        if (errores.length) {
            msgFormulario.textContent = errores.join(' ');
            return;
        }

        const payload = {};
        campos.forEach((id) => { payload[id] = $('#' + id).value.trim(); });
        payload.prioridad = $('#prioridad').value;
        if (esTransporte) {
            payload.id_vehiculo = $('#vehiculo').value || null;
            payload.id_chofer = $('#chofer').value || null;
            payload.id_enfermero = $('#enfermero').value || null;
        }

        $('#btnGuardar').disabled = true;
        if ($('#tipo_traslado').value === 'paciente') {
            const rp = await guardarPaciente();
            if (!rp.ok) {
                msgFormulario.textContent = mensajeDeError(rp);
                $('#btnGuardar').disabled = false;
                return;
            }
            payload.id_paciente = rp.data.id;
        } else {
            payload.id_objeto = $('#objeto').value;
        }

        const r = idEnEdicion === null
            ? await llamar(`${API}/traslados/index.php`, 'POST', payload)
            : await llamar(`${API}/traslados/index.php?id=${idEnEdicion}`, 'PUT', payload);
        $('#btnGuardar').disabled = false;

        if (!r.ok) {
            msgFormulario.textContent = mensajeDeError(r);
            return;
        }
        alert(idEnEdicion === null ? 'Traslado registrado correctamente.' : 'Traslado actualizado correctamente.');
        salirDeEdicion();
        await cargarTraslados();
    });

    // funciones para editar un traslado pendiente
    async function entrarEnEdicion(id) {
        const t = traslados.find((x) => Number(x.id_traslado) === id);
        if (!t) return;
        idEnEdicion = id;
        form.reset();

        $('#tipo_traslado').value = t.id_paciente ? 'paciente' : 'objeto';
        actualizarTipo();
        if (t.id_paciente) {
            const r = await llamar(`${API}/pacientes/index.php?id=${t.id_paciente}`);
            if (r.ok) cargarPaciente(r.data.datos);
        } else {
            $('#objeto').value = String(t.id_objeto);
        }
        campos.forEach((c) => { $('#' + c).value = (t[c] || '').toString().replace(/^(\d{2}:\d{2}):\d{2}$/, '$1'); });
        if (esTransporte) {
            $('#vehiculo').value = t.id_vehiculo || '';
            $('#chofer').value = t.id_chofer || '';
            $('#enfermero').value = t.id_enfermero || '';
        }
        actualizarOrgano();

        $('#tituloFormulario').textContent = `Editando traslado #${id}`;
        $('#btnGuardar').textContent = 'Guardar Cambios';
        $('#btnCancelarEdicion').classList.remove('oculta');
        $('#seccionFormulario').scrollIntoView({ behavior: 'smooth' });
    }

    function salirDeEdicion() {
        idEnEdicion = null;
        form.reset();
        $('#fecha').value = new Date().toISOString().substring(0, 10);
        $('#tituloFormulario').textContent = 'Nuevo Traslado';
        $('#btnGuardar').textContent = esDivision ? 'Enviar Solicitud' : 'Registrar Traslado';
        $('#btnCancelarEdicion').classList.add('oculta');
        $('#pacienteAviso').textContent = 'Si la C.I. ya existe, se cargan sus datos automáticamente.';
        msgFormulario.textContent = '';
        actualizarTipo();
    }
    $('#btnCancelarEdicion').addEventListener('click', salirDeEdicion);

    // carga y muestra de la tabla de traslados
    function filtrosActuales() {
        const p = new URLSearchParams();
        if ($('#filtroDocumento').value.trim()) p.set('documento', $('#filtroDocumento').value.trim());
        if ($('#filtroVehiculo').value) p.set('id_vehiculo', $('#filtroVehiculo').value);
        if ($('#filtroEstado').value) p.set('estado', $('#filtroEstado').value);
        if ($('#filtroDesde').value) p.set('fecha_desde', $('#filtroDesde').value);
        if ($('#filtroHasta').value) p.set('fecha_hasta', $('#filtroHasta').value);
        return p.toString();
    }

    async function cargarTraslados() {
        const qs = filtrosActuales();
        const r = await llamar(`${API}/traslados/index.php${qs ? '?' + qs : ''}`);
        traslados = r.ok ? (r.data.datos || []) : [];
        actualizarEstadisticas();
        renderizarTabla();
    }

    function actualizarEstadisticas() {
        const cuenta = (...estados) => traslados.filter((t) => estados.includes(t.estado)).length;
        $('#statTotalVal').textContent = traslados.length;
        $('#statPendientesVal').textContent = cuenta('Pendiente');
        $('#statEnCursoVal').textContent = cuenta('En Curso', 'Retornando');
        $('#statCompletadosVal').textContent = cuenta('Finalizado');
    }

    const BADGE_ESTADO = {
        'Pendiente': 'status-yellow', 'En Curso': 'status-blue pulse-badge', 'Retornando': 'status-blue',
        'Finalizado': 'status-green', 'Cancelado': 'status-red'
    };
    const BADGE_PRIORIDAD = { 'Alta': 'badge-prio-alta', 'Media': 'badge-prio-media', 'Baja': 'badge-prio-baja' };

    function botonesDeAccion(t) {
        if (!esTransporte) return '<span class="text-muted" style="font-size:0.8rem;">—</span>';
        const id = t.id_traslado;
        const boton = (accion, texto, clase = '') =>
            `<button class="btn-action ${clase}" data-accion="${accion}" data-id="${id}">${texto}</button>`;
        const acciones = [];
        if (t.estado === 'Pendiente') acciones.push(boton('editar', 'Editar'), boton('En Curso', 'Iniciar'));
        if (t.estado === 'En Curso') acciones.push(boton('Retornando', 'Registrar llegada'));
        if (t.estado === 'Retornando') acciones.push(boton('Finalizado', 'Finalizar'));
        if (['Pendiente', 'En Curso', 'Retornando'].includes(t.estado)) acciones.push(boton('Cancelado', 'Cancelar', 'btn-delete'));
        return acciones.length ? acciones.join(' ') : '<span class="text-muted" style="font-size:0.8rem;">Ninguna</span>';
    }

    function renderizarTabla() {
        if (traslados.length === 0) {
            tabla.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">No hay traslados que coincidan con la búsqueda.</td></tr>';
            return;
        }
        tabla.innerHTML = traslados.map((t) => {
            const carga = t.id_paciente
                ? `${esc(t.paciente_nombre)}<br><small>C.I. ${esc(t.paciente_documento)}</small>`
                : `${esc(t.objeto_nombre)} <small>(Objeto)</small>`;
            const recursos = (t.id_vehiculo || t.id_chofer)
                ? `<strong>Vehículo:</strong> ${esc(t.vehiculo_matricula || 'Sin asignar')}<br>
                   <strong>Chofer:</strong> ${esc(t.chofer_nombre || 'Sin asignar')}<br>
                   <strong>Enf:</strong> ${esc(t.enfermero_nombre || '—')}`
                : '<em>Pendiente de asignación</em>';
            return `<tr>
                <td><strong>#${esc(t.id_traslado)}</strong></td>
                <td>${carga}</td>
                <td><div style="font-size:0.85rem;">
                    <span style="color:#16a34a;">Desde: ${esc(t.sector_origen)} (P. ${esc(t.piso_origen)})</span><br>
                    <span style="color:#dc2626;">Hacia: ${esc(t.sector_destino)} - ${esc(t.direccion_destino)}</span></div></td>
                <td><div style="font-size:0.75rem; color:#475569;">${recursos}</div></td>
                <td><div style="font-size:0.75rem;">${esc(t.fecha)}<br>Sale ${hhmm(t.hora_salida)}<br>
                    Est. ${hhmm(t.hora_estimada_llegada)} / Real ${hhmm(t.hora_efectiva_llegada)}<br>
                    Retorno ${hhmm(t.hora_retorno)}</div></td>
                <td><span class="${BADGE_PRIORIDAD[t.prioridad] || ''}">${esc(t.prioridad)}</span></td>
                <td><span class="status-badge ${BADGE_ESTADO[t.estado] || ''}">${esc(t.estado)}</span></td>
                <td>${botonesDeAccion(t)}</td>
            </tr>`;
        }).join('');
    }

    // botones de accion en cada fila de la tabla
    tabla.addEventListener('click', async (e) => {
        const btn = e.target.closest('button[data-accion]');
        if (!btn) return;
        const id = parseInt(btn.dataset.id, 10);
        const accion = btn.dataset.accion;

        if (accion === 'editar') {
            await entrarEnEdicion(id);
            return;
        }
        if (accion === 'Cancelado') {
            if (!confirm(`¿Confirma la cancelación del traslado #${id}?`)) return;
            const r = await llamar(`${API}/traslados/index.php?id=${id}`, 'DELETE');
            if (!r.ok) alert(mensajeDeError(r));
        } else {
            const r = await llamar(`${API}/traslados/index.php?id=${id}&accion=estado`, 'PUT', { estado: accion });
            if (!r.ok) alert(mensajeDeError(r));
        }
        await cargarTraslados();
    });

    $('#formFiltros').addEventListener('submit', (e) => { e.preventDefault(); cargarTraslados(); });
    $('#btnLimpiarFiltros').addEventListener('click', () => {
        $('#formFiltros').reset();
        cargarTraslados();
    });

    // carga inicial al entrar a la pagina
    await cargarCatalogos();
    salirDeEdicion();
    await cargarTraslados();
    setInterval(() => { if (idEnEdicion === null) cargarTraslados(); }, 30000);
});
