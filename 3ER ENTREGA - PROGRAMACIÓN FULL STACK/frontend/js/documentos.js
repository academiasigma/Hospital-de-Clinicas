// logica del modulo de documentos, categorias y encuestas
document.addEventListener('DOMContentLoaded', async () => {
    const API = '../../api';
    const TAMANO_MAXIMO = 10 * 1024 * 1024; // 10 mb

    const usuario = await window.authPromise;
    if (!usuario) return;

    const puedeEscribirDocs = ['SUPERADMIN_IT', 'GESTOR_INTEGRAL', 'USUARIO_DIVISION'].includes(usuario.rol);
    const puedeGestionarCategorias = ['SUPERADMIN_IT', 'GESTOR_INTEGRAL'].includes(usuario.rol);

    // selectores del dom
    const $ = (sel) => document.querySelector(sel);
    const cajaAlerta = $('#alerta');

    const inputBuscar = $('#inputBuscar');
    const selectFiltroCategoria = $('#selectFiltroCategoria');
    const tablaCuerpo = $('#tablaCuerpo');

    const btnAbrirModalAlta = $('#btnAbrirModalAlta');
    const modalAlta = $('#modalAlta');
    const formAltaDocumento = $('#formAltaDocumento');
    const selectCategoriaForm = $('#id_categoria');
    const btnGuardarDocumento = $('#btnGuardarDocumento');

    const modalEditar = $('#modalEditar');
    const formEditarDocumento = $('#formEditarDocumento');
    const selectCategoriaEditForm = $('#edit_id_categoria');
    const btnActualizarDocumento = $('#btnActualizarDocumento');

    // elementos de categorias
    const formCategoria = $('#formCategoria');
    const inputCatId = $('#cat_id');
    const inputCatNombre = $('#cat_nombre');
    const btnGuardarCategoria = $('#btnGuardarCategoria');
    const btnCancelarCategoria = $('#btnCancelarCategoria');
    const tablaCategoriasCuerpo = $('#tablaCategoriasCuerpo');

    // elementos de encuestas
    const selectFiltroEncuestaCategoria = $('#selectFiltroEncuestaCategoria');
    const btnRefrescarEncuestas = $('#btnRefrescarEncuestas');
    const btnAbrirModalNuevaEncuesta = $('#btnAbrirModalNuevaEncuesta');
    const contenedorResultadosEncuestas = $('#contenedorResultadosEncuestas');

    const modalEncuestaAdmin = $('#modalEncuestaAdmin');
    const formEncuestaAdmin = $('#formEncuestaAdmin');
    const selectEncAdminCategoria = $('#enc_admin_categoria');

    const modalPreguntaAdmin = $('#modalPreguntaAdmin');
    const formPreguntaAdmin = $('#formPreguntaAdmin');
    const selectPregAdminTipo = $('#preg_admin_tipo');
    const grupoPregAdminOpciones = $('#grupoPregAdminOpciones');

    let documentosList = [];
    let categoriasList = [];
    let encuestasList = [];

    if (!puedeEscribirDocs && btnAbrirModalAlta) {
        btnAbrirModalAlta.classList.add('seccion-oculta');
    }
    if (!puedeGestionarCategorias && formCategoria) {
        formCategoria.classList.add('seccion-oculta');
    }
    if (!puedeGestionarCategorias && btnAbrirModalNuevaEncuesta) {
        btnAbrirModalNuevaEncuesta.classList.add('seccion-oculta');
    }

    // funciones auxiliares
    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) =>
        ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    const mostrarAlerta = (mensaje, tipo = 'error') => {
        cajaAlerta.textContent = mensaje;
        cajaAlerta.classList.remove('oculta', 'error', 'exito');
        cajaAlerta.classList.add(tipo);
        setTimeout(() => cajaAlerta.classList.add('oculta'), 5000);
    };

    const validarArchivoCliente = (archivo) => {
        if (archivo.type !== 'application/pdf' && !archivo.name.toLowerCase().endsWith('.pdf')) {
            return 'Formato no permitido. Seleccione únicamente un archivo PDF.';
        }
        if (archivo.size > TAMANO_MAXIMO) {
            return 'El archivo no puede superar los 10 MB.';
        }
        return null;
    };

    // cambio de pestañas entre documentos, categorias y encuestas
    document.querySelectorAll('.tab-modulo-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.tab-modulo-btn').forEach((b) => b.classList.remove('active'));
            btn.classList.add('active');

            const vista = btn.dataset.vista;
            $('#vistaDocumentos').classList.toggle('seccion-oculta', vista !== 'documentos');
            $('#vistaCategorias').classList.toggle('seccion-oculta', vista !== 'categorias');
            $('#vistaEncuestas').classList.toggle('seccion-oculta', vista !== 'encuestas');

            if (vista === 'encuestas') {
                cargarResultadosEncuestas();
            }
        });
    });

    // abm de categorias
    const poblarSelectoresCategorias = () => {
        const valorFiltroActual = selectFiltroCategoria.value || '0';
        const valorFiltroEncActual = selectFiltroEncuestaCategoria.value || '0';

        selectFiltroCategoria.innerHTML = '<option value="0">Todas las categorías</option>';
        selectFiltroEncuestaCategoria.innerHTML = '<option value="0">Todas las categorías con encuesta</option>';
        selectCategoriaForm.innerHTML = '<option value="">Seleccione una categoría</option>';
        selectCategoriaEditForm.innerHTML = '<option value="">Seleccione una categoría</option>';

        categoriasList.forEach((cat) => {
            const crearOpt = () => {
                const o = document.createElement('option');
                o.value = cat.id_categoria;
                o.textContent = cat.nombre;
                return o;
            };
            selectFiltroCategoria.appendChild(crearOpt());
            selectFiltroEncuestaCategoria.appendChild(crearOpt());
            selectCategoriaForm.appendChild(crearOpt());
            selectCategoriaEditForm.appendChild(crearOpt());
            if (selectEncAdminCategoria) selectEncAdminCategoria.appendChild(crearOpt());
        });

        selectFiltroCategoria.value = valorFiltroActual;
        selectFiltroEncuestaCategoria.value = valorFiltroEncActual;
    };

    const renderizarTablaCategorias = () => {
        if (categoriasList.length === 0) {
            tablaCategoriasCuerpo.innerHTML = '<tr><td colspan="3" class="text-center">No hay categorías registradas.</td></tr>';
            return;
        }
        tablaCategoriasCuerpo.innerHTML = categoriasList.map((cat) => {
            const acciones = puedeGestionarCategorias
                ? `<button type="button" class="btn-action btn-edit-cat" data-id="${esc(cat.id_categoria)}">Editar</button>
                   <button type="button" class="btn-action btn-delete btn-delete-cat" data-id="${esc(cat.id_categoria)}">Eliminar</button>`
                : '<span style="color:#64748b; font-size:0.8rem;">Solo lectura</span>';
            return `<tr>
                <td>#${esc(cat.id_categoria)}</td>
                <td><strong>${esc(cat.nombre)}</strong></td>
                <td>${acciones}</td>
            </tr>`;
        }).join('');
    };

    const cargarCategorias = async () => {
        try {
            const res = await fetch(`${API}/categorias/index.php`, { method: 'GET' });
            const data = await res.json();
            if (res.ok && data.status === 'ok') {
                categoriasList = data.datos || [];
                poblarSelectoresCategorias();
                renderizarTablaCategorias();
            }
        } catch (error) {
            mostrarAlerta('Error al obtener el listado de categorías.', 'error');
        }
    };

    const salirEdicionCategoria = () => {
        inputCatId.value = '';
        inputCatNombre.value = '';
        btnGuardarCategoria.textContent = 'Crear Categoría';
        btnCancelarCategoria.classList.add('seccion-oculta');
    };

    if (formCategoria) {
        formCategoria.addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = inputCatId.value.trim();
            const nombre = inputCatNombre.value.trim();

            if (!nombre) {
                mostrarAlerta('Ingrese el nombre de la categoría.', 'error');
                return;
            }

            const url = id ? `${API}/categorias/index.php?id=${id}` : `${API}/categorias/index.php`;
            const metodo = id ? 'PUT' : 'POST';

            try {
                const res = await fetch(url, {
                    method: metodo,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ nombre })
                });
                const data = await res.json();
                if (!res.ok) {
                    throw new Error(data.mensaje || 'No se pudo guardar la categoría.');
                }
                mostrarAlerta(data.mensaje, 'exito');
                salirEdicionCategoria();
                await cargarCategorias();
                await obtenerDocumentos();
            } catch (err) {
                mostrarAlerta(err.message, 'error');
            }
        });
    }

    if (btnCancelarCategoria) {
        btnCancelarCategoria.addEventListener('click', salirEdicionCategoria);
    }

    tablaCategoriasCuerpo.addEventListener('click', async (e) => {
        const btnEdit = e.target.closest('.btn-edit-cat');
        if (btnEdit) {
            const id = parseInt(btnEdit.dataset.id, 10);
            const cat = categoriasList.find((c) => parseInt(c.id_categoria, 10) === id);
            if (!cat) return;
            inputCatId.value = cat.id_categoria;
            inputCatNombre.value = cat.nombre;
            btnGuardarCategoria.textContent = 'Guardar Cambios';
            btnCancelarCategoria.classList.remove('seccion-oculta');
            inputCatNombre.focus();
            return;
        }

        const btnDel = e.target.closest('.btn-delete-cat');
        if (btnDel) {
            const id = parseInt(btnDel.dataset.id, 10);
            const cat = categoriasList.find((c) => parseInt(c.id_categoria, 10) === id);
            if (!confirm(`¿Confirma la eliminación de la categoría "${cat ? cat.nombre : '#' + id}"?`)) return;

            try {
                const res = await fetch(`${API}/categorias/index.php?id=${id}`, { method: 'DELETE' });
                const data = await res.json();
                if (!res.ok) {
                    throw new Error(data.mensaje || 'No se pudo eliminar la categoría.');
                }
                mostrarAlerta(data.mensaje, 'exito');
                if (inputCatId.value === String(id)) salirEdicionCategoria();
                await cargarCategorias();
            } catch (err) {
                mostrarAlerta(err.message, 'error');
            }
        }
    });

    // abm de documentos
    const renderizarTabla = () => {
        const busqueda = inputBuscar.value.toLowerCase().trim();
        const categoriaId = parseInt(selectFiltroCategoria.value, 10);

        const docsFiltrados = documentosList.filter((doc) => {
            const coincideTitulo = doc.titulo.toLowerCase().includes(busqueda);
            const coincideCategoria = categoriaId === 0 || parseInt(doc.id_categoria, 10) === categoriaId;
            return coincideTitulo && coincideCategoria;
        });

        tablaCuerpo.innerHTML = '';

        if (docsFiltrados.length === 0) {
            tablaCuerpo.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center">No se encontraron documentos con los criterios ingresados.</td>
                </tr>
            `;
            return;
        }

        docsFiltrados.forEach((doc) => {
            const tr = document.createElement('tr');
            const acciones = puedeEscribirDocs
                ? `<button type="button" class="btn-action btn-edit" data-id="${esc(doc.id_documento)}">Editar</button>
                   <button type="button" class="btn-action btn-delete" data-id="${esc(doc.id_documento)}">Eliminar</button>`
                : '<span style="color:#64748b; font-size:0.8rem;">Solo lectura</span>';

            tr.innerHTML = `
                <td>#${esc(doc.id_documento)}</td>
                <td><strong>${esc(doc.titulo)}</strong></td>
                <td>${esc(doc.categoria_nombre)}</td>
                <td>${esc(doc.autor_nombre || '—')}</td>
                <td>
                    <a href="../../${esc(doc.archivo)}" target="_blank" class="btn-action">Ver Archivo</a>
                </td>
                <td>${acciones}</td>
            `;
            tablaCuerpo.appendChild(tr);
        });
    };

    const obtenerDocumentos = async () => {
        try {
            const res = await fetch(`${API}/documentos/index.php`, { method: 'GET' });
            const data = await res.json();

            if (res.ok && data.status === 'ok') {
                documentosList = data.datos || [];
                renderizarTabla();
            } else {
                throw new Error(data.mensaje || 'Error al obtener documentos.');
            }
        } catch (error) {
            mostrarAlerta(error.message, 'error');
            tablaCuerpo.innerHTML = '<tr><td colspan="6" class="text-center">Error al cargar datos.</td></tr>';
        }
    };

    // subir un documento nuevo
    formAltaDocumento.addEventListener('submit', async (e) => {
        e.preventDefault();

        const titulo = $('#titulo').value.trim();
        const categoria = selectCategoriaForm.value;
        const inputArchivo = $('#archivo');

        if (!titulo || !categoria || inputArchivo.files.length === 0) {
            alert('Por favor complete todos los campos obligatorios y adjunte un archivo.');
            return;
        }

        const errorArchivo = validarArchivoCliente(inputArchivo.files[0]);
        if (errorArchivo) {
            alert(errorArchivo);
            return;
        }

        const formData = new FormData(formAltaDocumento);

        btnGuardarDocumento.disabled = true;
        btnGuardarDocumento.textContent = 'Guardando...';

        try {
            const res = await fetch(`${API}/documentos/index.php`, {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (!res.ok) {
                throw new Error(data.mensaje || 'Error al subir el documento.');
            }

            mostrarAlerta('Documento publicado con éxito.', 'exito');
            formAltaDocumento.reset();
            modalAlta.classList.add('oculta');
            await obtenerDocumentos();
        } catch (error) {
            alert(error.message);
        } finally {
            btnGuardarDocumento.disabled = false;
            btnGuardarDocumento.textContent = 'Guardar Documento';
        }
    });

    // editar un documento existente
    formEditarDocumento.addEventListener('submit', async (e) => {
        e.preventDefault();

        const id = $('#edit_id_documento').value;
        const titulo = $('#edit_titulo').value.trim();
        const categoria = selectCategoriaEditForm.value;
        const inputArchivoEdit = $('#edit_archivo');

        if (!id || !titulo || !categoria) {
            alert('Complete todos los campos obligatorios.');
            return;
        }

        const hayNuevoArchivo = inputArchivoEdit.files && inputArchivoEdit.files.length > 0;
        if (hayNuevoArchivo) {
            const errorArchivo = validarArchivoCliente(inputArchivoEdit.files[0]);
            if (errorArchivo) {
                alert(errorArchivo);
                return;
            }
        }

        btnActualizarDocumento.disabled = true;
        btnActualizarDocumento.textContent = 'Actualizando...';

        try {
            let res;
            if (hayNuevoArchivo) {
                const formData = new FormData(formEditarDocumento);
                formData.append('_method', 'PUT');
                res = await fetch(`${API}/documentos/index.php?id=${id}`, {
                    method: 'POST',
                    body: formData
                });
            } else {
                res = await fetch(`${API}/documentos/index.php?id=${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        titulo,
                        id_categoria: parseInt(categoria, 10)
                    })
                });
            }

            const data = await res.json();
            if (!res.ok) {
                throw new Error(data.mensaje || 'Error al actualizar el documento.');
            }

            mostrarAlerta('Documento actualizado correctamente.', 'exito');
            formEditarDocumento.reset();
            modalEditar.classList.add('oculta');
            await obtenerDocumentos();
        } catch (error) {
            alert(error.message);
        } finally {
            btnActualizarDocumento.disabled = false;
            btnActualizarDocumento.textContent = 'Guardar Cambios';
        }
    });

    // botones de editar y eliminar en la tabla de documentos
    tablaCuerpo.addEventListener('click', async (e) => {
        if (e.target.classList.contains('btn-delete')) {
            const id = e.target.dataset.id;
            if (!confirm(`¿Confirma la eliminación del documento con ID #${id}?`)) return;

            try {
                const res = await fetch(`${API}/documentos/index.php?id=${id}`, {
                    method: 'DELETE'
                });
                const data = await res.json();

                if (!res.ok) {
                    throw new Error(data.mensaje || 'No se pudo eliminar el documento.');
                }

                mostrarAlerta('Documento eliminado correctamente.', 'exito');
                await obtenerDocumentos();
            } catch (error) {
                mostrarAlerta(error.message, 'error');
            }
        }

        if (e.target.classList.contains('btn-edit')) {
            const id = parseInt(e.target.dataset.id, 10);
            const doc = documentosList.find((d) => parseInt(d.id_documento, 10) === id);
            if (!doc) return;

            $('#edit_id_documento').value = doc.id_documento;
            $('#edit_titulo').value = doc.titulo;
            $('#edit_id_categoria').value = doc.id_categoria;
            $('#edit_archivo').value = '';

            modalEditar.classList.remove('oculta');
        }
    });

    // resultados y abm de encuestas
    const renderizarPreguntaResultado = (p, idEncuesta) => {
        const accionesPregunta = puedeGestionarCategorias
            ? `<div style="display:flex; gap:0.35rem; flex-shrink:0;">
                   <button type="button" class="btn-action btn-edit-preg" data-id="${esc(p.id_pregunta)}" data-encuesta="${esc(idEncuesta)}">Editar</button>
                   <button type="button" class="btn-action btn-delete btn-delete-preg" data-id="${esc(p.id_pregunta)}">Eliminar</button>
               </div>`
            : '';

        const cabecera = `
            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:0.75rem;">
                <div>
                    <strong>${esc(p.enunciado)}</strong>
                    <span class="badge" style="background:#f1f5f9; color:#475569; margin-left:0.35rem;">${esc(p.tipo)}</span>
                </div>
                ${accionesPregunta}
            </div>
        `;

        if (p.tipo === 'Si/No') {
            const total = (p.si || 0) + (p.no || 0);
            const porcSi = total > 0 ? Math.round((p.si / total) * 100) : 0;
            const porcNo = total > 0 ? 100 - porcSi : 0;
            return `
                <div class="pregunta-resultado">
                    ${cabecera}
                    <div style="font-size:0.85rem; color:#475569; margin-top:0.25rem;">
                        Sí: <strong>${p.si}</strong> (${porcSi}%) &nbsp;|&nbsp;
                        No: <strong>${p.no}</strong> (${porcNo}%) &nbsp;|&nbsp;
                        Total: ${total}
                    </div>
                    <div class="barra-progreso"><div style="width:${porcSi}%;"></div></div>
                </div>
            `;
        }

        if (p.tipo === 'Escala_1_5') {
            const prom = p.promedio !== null ? `${p.promedio} / 5` : 'Sin calificaciones';
            const porc = p.promedio !== null ? Math.round((p.promedio / 5) * 100) : 0;
            const dist = p.distribucion || {};
            const detalle = [1, 2, 3, 4, 5].map((n) => `${n}★: ${dist[n] || 0}`).join(' · ');
            return `
                <div class="pregunta-resultado">
                    ${cabecera}
                    <div style="font-size:0.85rem; color:#475569; margin-top:0.25rem;">
                        Promedio: <strong>${esc(prom)}</strong> (${p.total_respuestas} respuestas) — <small>${esc(detalle)}</small>
                    </div>
                    <div class="barra-progreso"><div style="width:${porc}%;"></div></div>
                </div>
            `;
        }

        if (p.tipo === 'Opcion_Multiple') {
            const conteo = p.conteo_opciones || {};
            const detalleOps = Object.entries(conteo).map(([op, cant]) => `${esc(op)}: <strong>${cant}</strong>`).join(' &nbsp;|&nbsp; ');
            return `
                <div class="pregunta-resultado">
                    ${cabecera}
                    <div style="font-size:0.85rem; color:#475569; margin-top:0.25rem;">
                        ${detalleOps || 'Sin opciones registradas'}
                    </div>
                </div>
            `;
        }

        const textos = (p.respuestas || []);
        const items = textos.length > 0
            ? `<ul class="lista-textos">${textos.slice(0, 10).map((t) => `<li>${esc(t)}</li>`).join('')}</ul>`
            : '<p style="font-size:0.85rem; color:#64748b; margin-top:0.25rem;">Sin comentarios registrados.</p>';
        return `
            <div class="pregunta-resultado">
                ${cabecera}
                ${items}
            </div>
        `;
    };

    const cargarResultadosEncuestas = async (preservarScroll = true) => {
        const scrollPrevio = window.scrollY;
        if (!preservarScroll || !contenedorResultadosEncuestas.children.length) {
            contenedorResultadosEncuestas.innerHTML = '<p class="text-center">Consultando resultados...</p>';
        }
        const idCat = parseInt(selectFiltroEncuestaCategoria.value, 10);
        const qs = idCat > 0 ? `&id_categoria=${idCat}` : '';

        try {
            const res = await fetch(`${API}/encuestas/index.php?resultados=1${qs}`, { method: 'GET' });
            const data = await res.json();

            if (!res.ok || data.status !== 'ok') {
                throw new Error(data.mensaje || 'No se pudieron obtener los resultados.');
            }

            encuestasList = data.datos || [];
            if (encuestasList.length === 0) {
                contenedorResultadosEncuestas.innerHTML = '<p class="text-center">No hay encuestas registradas para esta categoría.</p>';
                return;
            }

            contenedorResultadosEncuestas.innerHTML = encuestasList.map((enc) => {
                const preguntasHTML = (enc.preguntas || []).length > 0
                    ? enc.preguntas.map((p) => renderizarPreguntaResultado(p, enc.id_encuesta)).join('')
                    : '<p style="font-size:0.85rem; color:#64748b;">Esta encuesta aún no tiene preguntas cargadas.</p>';
                const obs = enc.observaciones || [];
                const obsHTML = obs.length > 0
                    ? `<div class="pregunta-resultado">
                           <strong>Observaciones generales de los pacientes (${obs.length}):</strong>
                           <ul class="lista-textos">
                               ${obs.slice(0, 10).map((o) => `<li><small style="color:#64748b;">[${esc(o.fecha_hora)}]</small> ${esc(o.texto)}</li>`).join('')}
                           </ul>
                       </div>`
                    : '';

                const botonesEncuesta = puedeGestionarCategorias
                    ? `<div style="margin-top:0.5rem; display:flex; gap:0.35rem; justify-content:flex-end; flex-wrap:wrap;">
                           <button type="button" class="btn-action btn-add-preg" data-encuesta="${esc(enc.id_encuesta)}">+ Agregar Pregunta</button>
                           <button type="button" class="btn-action btn-edit-enc" data-encuesta="${esc(enc.id_encuesta)}">Editar Encuesta</button>
                           <button type="button" class="btn-action btn-delete btn-delete-enc" data-encuesta="${esc(enc.id_encuesta)}">Eliminar Encuesta</button>
                       </div>`
                    : '';

                const colorEstado = enc.estado === 'Activa'
                    ? 'background:#dcfce7; color:#166534;'
                    : (enc.estado === 'Borrador' ? 'background:#fef3c7; color:#92400e;' : 'background:#fee2e2; color:#991b1b;');

                return `
                    <article class="encuesta-card" id="encuesta-card-${esc(enc.id_encuesta)}">
                        <div class="encuesta-header">
                            <div>
                                <span class="badge badge-publico">${esc(enc.categoria_nombre)}</span>
                                <h3 style="margin-top:0.35rem;">${esc(enc.comentarios || 'Encuesta de Satisfacción')}</h3>
                            </div>
                            <div style="text-align:right;">
                                <span class="badge" style="${colorEstado}">Estado: ${esc(enc.estado)}</span>
                                <div style="font-size:0.85rem; color:#475569; margin-top:0.25rem;">
                                    Encuestas respondidas: <strong>${esc(enc.total_resoluciones)}</strong>
                                </div>
                                ${botonesEncuesta}
                            </div>
                        </div>
                        ${preguntasHTML}
                        ${obsHTML}
                    </article>
                `;
            }).join('');

            if (preservarScroll) {
                window.scrollTo({ top: scrollPrevio, behavior: 'instant' });
            }
        } catch (err) {
            contenedorResultadosEncuestas.innerHTML = `<p class="text-center" style="color:#b91c1c;">${esc(err.message)}</p>`;
        }
    };

    selectFiltroEncuestaCategoria.addEventListener('change', () => cargarResultadosEncuestas(false));
    btnRefrescarEncuestas.addEventListener('click', () => cargarResultadosEncuestas(true));

    // Modales de administración de encuestas y preguntas
    if (btnAbrirModalNuevaEncuesta) {
        btnAbrirModalNuevaEncuesta.addEventListener('click', () => {
            formEncuestaAdmin.reset();
            $('#enc_admin_id').value = '';
            $('#tituloModalEncuestaAdmin').textContent = 'Nueva Encuesta por Categoría';
            $('#grupoEncAdminCategoria').classList.remove('seccion-oculta');
            modalEncuestaAdmin.classList.remove('oculta');
        });
    }
    $('#btnCerrarModalEncuestaAdmin').addEventListener('click', () => modalEncuestaAdmin.classList.add('oculta'));
    $('#btnCancelarModalEncuestaAdmin').addEventListener('click', () => modalEncuestaAdmin.classList.add('oculta'));

    formEncuestaAdmin.addEventListener('submit', async (e) => {
        e.preventDefault();
        const idEnc = $('#enc_admin_id').value.trim();
        const id_categoria = parseInt(selectEncAdminCategoria.value, 10);
        const comentarios = $('#enc_admin_comentarios').value.trim();
        const estado = $('#enc_admin_estado').value;

        if (!comentarios || (!idEnc && !id_categoria)) {
            alert('Complete el título y la categoría de la encuesta.');
            return;
        }

        try {
            const url = idEnc ? `${API}/encuestas/index.php?id=${idEnc}` : `${API}/encuestas/index.php?recurso=encuesta`;
            const metodo = idEnc ? 'PUT' : 'POST';
            const res = await fetch(url, {
                method: metodo,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_categoria, comentarios, estado })
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.mensaje || 'Error al guardar la encuesta.');

            mostrarAlerta(data.mensaje, 'exito');
            modalEncuestaAdmin.classList.add('oculta');
            await cargarResultadosEncuestas();
        } catch (err) {
            alert(err.message);
        }
    });

    selectPregAdminTipo.addEventListener('change', () => {
        grupoPregAdminOpciones.classList.toggle('seccion-oculta', selectPregAdminTipo.value !== 'Opcion_Multiple');
    });
    $('#btnCerrarModalPreguntaAdmin').addEventListener('click', () => modalPreguntaAdmin.classList.add('oculta'));
    $('#btnCancelarModalPreguntaAdmin').addEventListener('click', () => modalPreguntaAdmin.classList.add('oculta'));

    formPreguntaAdmin.addEventListener('submit', async (e) => {
        e.preventDefault();
        const idPreg = $('#preg_admin_id').value.trim();
        const id_encuesta = parseInt($('#preg_admin_id_encuesta').value, 10);
        const enunciado = $('#preg_admin_enunciado').value.trim();
        const tipo = selectPregAdminTipo.value;
        const opciones = $('#preg_admin_opciones').value
            .split('\n')
            .map((s) => s.trim())
            .filter((s) => s !== '');

        if (!enunciado) {
            alert('Ingrese el enunciado de la pregunta.');
            return;
        }
        if (tipo === 'Opcion_Multiple' && opciones.length < 2) {
            alert('Ingrese al menos 2 opciones (una por línea).');
            return;
        }

        try {
            const url = idPreg
                ? `${API}/encuestas/index.php?id=${idPreg}&recurso=pregunta`
                : `${API}/encuestas/index.php?recurso=pregunta`;
            const metodo = idPreg ? 'PUT' : 'POST';
            const res = await fetch(url, {
                method: metodo,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_encuesta, enunciado, tipo, opciones, recurso: 'pregunta' })
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.mensaje || 'Error al guardar la pregunta.');

            mostrarAlerta(data.mensaje, 'exito');
            modalPreguntaAdmin.classList.add('oculta');
            await cargarResultadosEncuestas();
        } catch (err) {
            alert(err.message);
        }
    });

    contenedorResultadosEncuestas.addEventListener('click', async (e) => {
        const btnEditEnc = e.target.closest('.btn-edit-enc');
        if (btnEditEnc) {
            const idEnc = parseInt(btnEditEnc.dataset.encuesta, 10);
            const enc = encuestasList.find((x) => parseInt(x.id_encuesta, 10) === idEnc);
            if (!enc) return;
            $('#enc_admin_id').value = enc.id_encuesta;
            selectEncAdminCategoria.value = enc.id_categoria;
            $('#enc_admin_comentarios').value = enc.comentarios || '';
            $('#enc_admin_estado').value = enc.estado;
            $('#tituloModalEncuestaAdmin').textContent = `Editar Encuesta #${enc.id_encuesta}`;
            $('#grupoEncAdminCategoria').classList.add('seccion-oculta');
            modalEncuestaAdmin.classList.remove('oculta');
            return;
        }

        const btnAddPreg = e.target.closest('.btn-add-preg');
        if (btnAddPreg) {
            formPreguntaAdmin.reset();
            $('#preg_admin_id').value = '';
            $('#preg_admin_id_encuesta').value = btnAddPreg.dataset.encuesta;
            $('#tituloModalPreguntaAdmin').textContent = 'Agregar Pregunta a la Encuesta';
            grupoPregAdminOpciones.classList.add('seccion-oculta');
            modalPreguntaAdmin.classList.remove('oculta');
            return;
        }

        const btnEditPreg = e.target.closest('.btn-edit-preg');
        if (btnEditPreg) {
            const idPreg = parseInt(btnEditPreg.dataset.id, 10);
            const idEnc = parseInt(btnEditPreg.dataset.encuesta, 10);
            const enc = encuestasList.find((x) => parseInt(x.id_encuesta, 10) === idEnc);
            const preg = enc && (enc.preguntas || []).find((p) => parseInt(p.id_pregunta, 10) === idPreg);
            if (!preg) return;

            $('#preg_admin_id').value = preg.id_pregunta;
            $('#preg_admin_id_encuesta').value = idEnc;
            $('#preg_admin_enunciado').value = preg.enunciado;
            selectPregAdminTipo.value = preg.tipo;
            $('#preg_admin_opciones').value = (preg.opciones || []).join('\n');
            grupoPregAdminOpciones.classList.toggle('seccion-oculta', preg.tipo !== 'Opcion_Multiple');
            $('#tituloModalPreguntaAdmin').textContent = `Editar Pregunta #${preg.id_pregunta}`;
            modalPreguntaAdmin.classList.remove('oculta');
            return;
        }

        const btnDelPreg = e.target.closest('.btn-delete-preg');
        if (btnDelPreg) {
            const idPreg = parseInt(btnDelPreg.dataset.id, 10);
            if (!confirm(`¿Confirma la eliminación de la pregunta #${idPreg}?`)) return;
            try {
                const res = await fetch(`${API}/encuestas/index.php?id=${idPreg}&recurso=pregunta`, { method: 'DELETE' });
                const data = await res.json();
                if (!res.ok) throw new Error(data.mensaje || 'No se pudo eliminar la pregunta.');
                mostrarAlerta(data.mensaje, 'exito');
                await cargarResultadosEncuestas();
            } catch (err) {
                mostrarAlerta(err.message, 'error');
            }
            return;
        }

        const btnDelEnc = e.target.closest('.btn-delete-enc');
        if (btnDelEnc) {
            const idEnc = parseInt(btnDelEnc.dataset.encuesta, 10);
            if (!confirm(`¿Confirma la eliminación completa de la encuesta #${idEnc} y sus preguntas?`)) return;
            try {
                const res = await fetch(`${API}/encuestas/index.php?id=${idEnc}`, { method: 'DELETE' });
                const data = await res.json();
                if (!res.ok) throw new Error(data.mensaje || 'No se pudo eliminar la encuesta.');
                mostrarAlerta(data.mensaje, 'exito');
                await cargarResultadosEncuestas();
            } catch (err) {
                mostrarAlerta(err.message, 'error');
            }
        }
    });

    // eventos de los modales y filtros
    inputBuscar.addEventListener('input', renderizarTabla);
    selectFiltroCategoria.addEventListener('change', renderizarTabla);

    btnAbrirModalAlta.addEventListener('click', () => modalAlta.classList.remove('oculta'));
    $('#btnCerrarModalAlta').addEventListener('click', () => modalAlta.classList.add('oculta'));
    $('#btnCancelarModalAlta').addEventListener('click', () => modalAlta.classList.add('oculta'));

    $('#btnCerrarModalEditar').addEventListener('click', () => modalEditar.classList.add('oculta'));
    $('#btnCancelarModalEditar').addEventListener('click', () => modalEditar.classList.add('oculta'));

    // Carga inicial
    await cargarCategorias();
    await obtenerDocumentos();
});
