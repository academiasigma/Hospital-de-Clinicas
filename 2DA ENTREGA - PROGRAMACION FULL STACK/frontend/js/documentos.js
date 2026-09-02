document.addEventListener('DOMContentLoaded', () => {
    const txtNombre = document.querySelector('#txtNombre');
    const btnCerrarSesion = document.querySelector('#btnCerrarSesion');

    const inputBuscar = document.querySelector('#inputBuscar');
    const selectFiltroCategoria = document.querySelector('#selectFiltroCategoria');
    const tablaCuerpo = document.querySelector('#tablaCuerpo');
    const cajaAlerta = document.querySelector('#alerta');

    const btnAbrirModalAlta = document.querySelector('#btnAbrirModalAlta');
    const modalAlta = document.querySelector('#modalAlta');
    const btnCerrarModalAlta = document.querySelector('#btnCerrarModalAlta');
    const btnCancelarModalAlta = document.querySelector('#btnCancelarModalAlta');
    const formAltaDocumento = document.querySelector('#formAltaDocumento');
    const selectCategoriaForm = document.querySelector('#id_categoria');
    const btnGuardarDocumento = document.querySelector('#btnGuardarDocumento');

    const modalEditar = document.querySelector('#modalEditar');
    const btnCerrarModalEditar = document.querySelector('#btnCerrarModalEditar');
    const btnCancelarModalEditar = document.querySelector('#btnCancelarModalEditar');
    const formEditarDocumento = document.querySelector('#formEditarDocumento');
    const selectCategoriaEditForm = document.querySelector('#edit_id_categoria');
    const btnActualizarDocumento = document.querySelector('#btnActualizarDocumento');

    const modalQR = document.querySelector('#modalQR');
    const btnCerrarModalQR = document.querySelector('#btnCerrarModalQR');
    const qrModalTitulo = document.querySelector('#qrModalTitulo');
    const qrImagenContainer = document.querySelector('#qrImagenContainer');
    const qrModalCodigo = document.querySelector('#qrModalCodigo');
    const qrModalSub = document.querySelector('#qrModalSub');
    const btnQRGeneral = document.querySelector('#btnQRGeneral');

    let documentosList = [];
    let categoriasList = [];

    const mostrarAlerta = (mensaje, tipo = 'error') => {
        cajaAlerta.textContent = mensaje;
        cajaAlerta.classList.remove('oculta', 'error', 'exito');
        cajaAlerta.classList.add(tipo);
        setTimeout(() => cajaAlerta.classList.add('oculta'), 5000);
    };

    

    const cargarCategorias = async () => {
        try {
            const res = await fetch('../../api/categorias/index.php', { method: 'GET' });
            const data = await res.json();

            if (res.ok && data.status === 'ok') {
                categoriasList = data.datos;

                categoriasList.forEach((cat) => {
                    const optFiltro = document.createElement('option');
                    optFiltro.value = cat.id_categoria;
                    optFiltro.textContent = cat.nombre;
                    selectFiltroCategoria.appendChild(optFiltro);

                    const optForm = document.createElement('option');
                    optForm.value = cat.id_categoria;
                    optForm.textContent = cat.nombre;
                    selectCategoriaForm.appendChild(optForm);

                    const optEdit = document.createElement('option');
                    optEdit.value = cat.id_categoria;
                    optEdit.textContent = cat.nombre;
                    selectCategoriaEditForm.appendChild(optEdit);
                });
            }
        } catch (error) {
            mostrarAlerta('Error al obtener el listado de categorías.', 'error');
        }
    };

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
                    <td colspan="5" class="text-center">No se encontraron documentos con los criterios ingresados.</td>
                </tr>
            `;
            return;
        }

        docsFiltrados.forEach((doc) => {
            const tr = document.createElement('tr');

            

            tr.innerHTML = `
                <td>#${doc.id_documento}</td>
                <td><strong>${doc.titulo}</strong></td>
                <td>${doc.categoria_nombre}</td>
                
                <td>
                    <a href="../../${doc.archivo}" target="_blank" class="btn-action">Ver Archivo</a>
                </td>
                <td>
                    <button class="btn-action btn-edit" data-id="${doc.id_documento}">Editar</button>
                    <button class="btn-action btn-delete" data-id="${doc.id_documento}">Eliminar</button>
                </td>
            `;

            tablaCuerpo.appendChild(tr);
        });
    };

    const obtenerDocumentos = async () => {
        try {
            const res = await fetch('../../api/documentos/index.php', { method: 'GET' });
            const data = await res.json();

            if (res.ok && data.status === 'ok') {
                documentosList = data.datos;
                renderizarTabla();
            } else {
                throw new Error(data.mensaje || 'Error al obtener documentos.');
            }
        } catch (error) {
            mostrarAlerta(error.message, 'error');
            tablaCuerpo.innerHTML = `<tr><td colspan="5" class="text-center">Error al cargar datos.</td></tr>`;
        }
    };

    formAltaDocumento.addEventListener('submit', async (e) => {
        e.preventDefault();

        const titulo = document.querySelector('#titulo').value.trim();
        const categoria = selectCategoriaForm.value;
        const inputArchivo = document.querySelector('#archivo');

        if (!titulo || !categoria || inputArchivo.files.length === 0) {
            alert('Por favor complete todos los campos obligatorios y adjunte un archivo.');
            return;
        }

        const formData = new FormData(formAltaDocumento);

        btnGuardarDocumento.disabled = true;
        btnGuardarDocumento.textContent = 'Guardando...';

        try {
            const res = await fetch('../../api/documentos/index.php', {
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

    formEditarDocumento.addEventListener('submit', async (e) => {
        e.preventDefault();

        const id = document.querySelector('#edit_id_documento').value;
        const titulo = document.querySelector('#edit_titulo').value.trim();
        const categoria = selectCategoriaEditForm.value;

        if (!id || !titulo || !categoria) {
            alert('Complete todos los campos obligatorios.');
            return;
        }

        const formData = new FormData(formEditarDocumento);

        btnActualizarDocumento.disabled = true;
        btnActualizarDocumento.textContent = 'Actualizando...';

        try {
            const res = await fetch('../../api/documentos/actualizar.php', {
                method: 'POST',
                body: formData
            });

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

    tablaCuerpo.addEventListener('click', async (e) => {
        if (e.target.classList.contains('btn-delete')) {
            const id = e.target.dataset.id;
            const confirmar = confirm(`¿Confirma la eliminación del documento con ID #${id}?`);

            if (!confirmar) return;

            try {
                const res = await fetch(`../../api/documentos/eliminar.php?id=${id}`, {
                    method: 'POST'
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
            const doc = documentosList.find(d => parseInt(d.id_documento, 10) === id);

            if (!doc) return;

            document.querySelector('#edit_id_documento').value = doc.id_documento;
            document.querySelector('#edit_titulo').value = doc.titulo;
            document.querySelector('#edit_id_categoria').value = doc.id_categoria;
            
            document.querySelector('#edit_archivo').value = '';

            modalEditar.classList.remove('oculta');
        }


    });

    // Evento del QR general para todo el Portal de Pacientes
    btnQRGeneral.addEventListener('click', () => {
        qrModalTitulo.textContent = "QR General del Hospital de Clínicas";
        // qrModalCodigo.textContent = "PORTAL_INVITADOS";
        qrModalSub.textContent = "Este QR único redirige a los pacientes al Portal de Invitados de SIGSM.";

        const urlPortalGeneral = `${window.location.origin}/sigsm/frontend/html/portal-invitados.html`;

        qrImagenContainer.innerHTML = `
            <a href="${urlPortalGeneral}" target="_blank" title="Abrir portal de pacientes">
                <img 
                    src="../img/qr.png" 
                    alt="Código QR General" 
                    style="border: 1px solid #cbd5e1; padding: 6px; border-radius: 4px; max-width: 180px;"
                >
            </a>
            <div style="margin-top: 0.5rem;">
                <a href="${urlPortalGeneral}" target="_blank" style="font-size:0.8rem; color:#0284c7; font-weight:600; text-decoration:none;">
                    Abrir Portal de Invitados &rarr;
                </a>
            </div>
        `;

        modalQR.classList.remove('oculta');
    });

    inputBuscar.addEventListener('input', renderizarTabla);
    selectFiltroCategoria.addEventListener('change', renderizarTabla);

    btnAbrirModalAlta.addEventListener('click', () => modalAlta.classList.remove('oculta'));
    btnCerrarModalAlta.addEventListener('click', () => modalAlta.classList.add('oculta'));
    btnCancelarModalAlta.addEventListener('click', () => modalAlta.classList.add('oculta'));
    
    btnCerrarModalEditar.addEventListener('click', () => modalEditar.classList.add('oculta'));
    btnCancelarModalEditar.addEventListener('click', () => modalEditar.classList.add('oculta'));

    btnCerrarModalQR.addEventListener('click', () => modalQR.classList.add('oculta'));

    window.authPromise.then((user) => {
        if (user) {
            cargarCategorias();
            obtenerDocumentos();
        }
    });
});
