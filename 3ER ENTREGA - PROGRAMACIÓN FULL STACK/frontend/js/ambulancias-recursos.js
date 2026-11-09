/**
 * Gestión de choferes, enfermeros y vehículos (CRUD completo sobre la API REST).
 * Una única pantalla con pestañas: cada recurso se describe en la configuración CONFIG.
 * Acceso: solo SUPERADMIN_IT y GESTOR_INTEGRAL (el backend también lo exige).
 */
document.addEventListener('DOMContentLoaded', async () => {
    const API = '../../api';
    const $ = (sel) => document.querySelector(sel);

    const usuario = await window.authPromise;
    if (!usuario) return;
    if (!['SUPERADMIN_IT', 'GESTOR_INTEGRAL'].includes(usuario.rol)) {
        window.location.href = 'ambulancias.html';
        return;
    }

    const CONFIG = {
        choferes: {
            etiqueta: 'chofer', pk: 'id_chofer',
            campos: [
                { id: 'nombre', texto: 'Nombre completo', tipo: 'text', max: 100 },
                { id: 'telefono', texto: 'Teléfono', tipo: 'text', max: 20, patron: /^[0-9+\s-]{7,20}$/, ayuda: 'Solo números, +, espacios y guiones.' },
                { id: 'licencia', texto: 'Licencia de conducir', tipo: 'text', max: 50 }
            ]
        },
        enfermeros: {
            etiqueta: 'enfermero', pk: 'id_enfermero',
            campos: [
                { id: 'nombre', texto: 'Nombre completo', tipo: 'text', max: 100 },
                { id: 'telefono', texto: 'Teléfono', tipo: 'text', max: 20, patron: /^[0-9+\s-]{7,20}$/, ayuda: 'Solo números, +, espacios y guiones.' },
                { id: 'matricula_profesional', texto: 'Matrícula profesional', tipo: 'text', max: 50 }
            ]
        },
        vehiculos: {
            etiqueta: 'vehículo', pk: 'id_vehiculo',
            campos: [
                { id: 'tipo_vehiculo', texto: 'Tipo', tipo: 'select', opciones: ['Ambulancia', 'Auto'] },
                { id: 'matricula', texto: 'Matrícula', tipo: 'text', max: 20 },
                { id: 'estado', texto: 'Estado', tipo: 'select', opciones: ['Disponible', 'En Traslado', 'Mantenimiento', 'Fuera de Servicio'] }
            ]
        }
    };

    let recurso = 'choferes';
    let filas = [];
    let idEnEdicion = null;

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
    const mensajeDeError = (r) => (r.data.errores && r.data.errores.length ? r.data.errores.join(' ') : r.data.mensaje || 'Error inesperado.');

    function dibujarFormulario() {
        const cfg = CONFIG[recurso];
        $('#camposForm').innerHTML = cfg.campos.map((c) => {
            const control = c.tipo === 'select'
                ? `<select id="rec_${c.id}">${c.opciones.map((o) => `<option>${esc(o)}</option>`).join('')}</select>`
                : `<input type="text" id="rec_${c.id}" maxlength="${c.max}">`;
            return `<div class="form-group"><label for="rec_${c.id}">${esc(c.texto)} *</label>${control}</div>`;
        }).join('');
        $('#tituloForm').textContent = idEnEdicion === null ? `Nuevo ${cfg.etiqueta}` : `Editando ${cfg.etiqueta} #${idEnEdicion}`;
        $('#btnCancelarRecurso').classList.toggle('oculta', idEnEdicion === null);
        $('#msgRecurso').textContent = '';
    }

    function dibujarTabla() {
        const cfg = CONFIG[recurso];
        $('#tituloLista').textContent = recurso.charAt(0).toUpperCase() + recurso.slice(1);
        $('#encabezadoTabla').innerHTML = '<th>ID</th>' + cfg.campos.map((c) => `<th>${esc(c.texto)}</th>`).join('') + '<th>Acciones</th>';
        if (filas.length === 0) {
            $('#cuerpoTabla').innerHTML = `<tr><td colspan="${cfg.campos.length + 2}" class="text-center text-muted py-4">Sin registros.</td></tr>`;
            return;
        }
        $('#cuerpoTabla').innerHTML = filas.map((f) => `<tr>
            <td>#${esc(f[cfg.pk])}</td>
            ${cfg.campos.map((c) => `<td>${esc(f[c.id])}</td>`).join('')}
            <td>
                <button class="btn-action" data-accion="editar" data-id="${esc(f[cfg.pk])}">Editar</button>
                <button class="btn-action btn-delete" data-accion="eliminar" data-id="${esc(f[cfg.pk])}">Eliminar</button>
            </td></tr>`).join('');
    }

    async function cargar() {
        const r = await llamar(`${API}/${recurso}/index.php`);
        filas = r.ok ? r.data.datos : [];
        dibujarTabla();
    }

    async function cambiarRecurso(nuevo) {
        recurso = nuevo;
        idEnEdicion = null;
        document.querySelectorAll('.tab-recurso').forEach((b) => {
            b.className = 'btn-small tab-recurso ' + (b.dataset.recurso === nuevo ? 'btn-primary' : 'btn-secondary');
        });
        dibujarFormulario();
        await cargar();
    }

    document.querySelectorAll('.tab-recurso').forEach((b) => b.addEventListener('click', () => cambiarRecurso(b.dataset.recurso)));

    $('#formRecurso').addEventListener('submit', async (e) => {
        e.preventDefault();
        const cfg = CONFIG[recurso];
        const datos = {};
        const errores = [];
        cfg.campos.forEach((c) => {
            const valor = $('#rec_' + c.id).value.trim();
            datos[c.id] = valor;
            if (!valor) errores.push(`Complete "${c.texto}".`);
            else if (c.patron && !c.patron.test(valor)) errores.push(`${c.texto}: ${c.ayuda}`);
        });
        if (errores.length) {
            $('#msgRecurso').textContent = errores.join(' ');
            return;
        }

        const r = idEnEdicion === null
            ? await llamar(`${API}/${recurso}/index.php`, 'POST', datos)
            : await llamar(`${API}/${recurso}/index.php?id=${idEnEdicion}`, 'PUT', datos);
        if (!r.ok) {
            $('#msgRecurso').textContent = mensajeDeError(r);
            return;
        }
        idEnEdicion = null;
        dibujarFormulario();
        await cargar();
    });

    $('#btnCancelarRecurso').addEventListener('click', () => { idEnEdicion = null; dibujarFormulario(); });

    $('#cuerpoTabla').addEventListener('click', async (e) => {
        const btn = e.target.closest('button[data-accion]');
        if (!btn) return;
        const cfg = CONFIG[recurso];
        const id = parseInt(btn.dataset.id, 10);

        if (btn.dataset.accion === 'editar') {
            const fila = filas.find((f) => Number(f[cfg.pk]) === id);
            idEnEdicion = id;
            dibujarFormulario();
            cfg.campos.forEach((c) => { $('#rec_' + c.id).value = fila[c.id]; });
        } else if (confirm(`¿Eliminar el ${cfg.etiqueta} #${id}?`)) {
            const r = await llamar(`${API}/${recurso}/index.php?id=${id}`, 'DELETE');
            if (!r.ok) alert(mensajeDeError(r));
            await cargar();
        }
    });

    await cambiarRecurso('choferes');
});
