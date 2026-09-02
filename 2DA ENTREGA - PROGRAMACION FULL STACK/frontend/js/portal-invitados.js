document.addEventListener('DOMContentLoaded', () => {
    // Selectores
    const inputBuscar = document.querySelector('#inputBuscar');
    const categoryTabs = document.querySelector('#categoryTabs');
    const documentsGrid = document.querySelector('#documentsGrid');

    // Selectores del modal de encuestas
    const modalEncuesta = document.querySelector('#modalEncuesta');
    const btnCerrarModalEncuesta = document.querySelector('#btnCerrarModalEncuesta');
    const btnCancelarEncuesta = document.querySelector('#btnCancelarEncuesta');
    const formEncuesta = document.querySelector('#formEncuesta');
    const surveyTitle = document.querySelector('#surveyTitle');
    const surveyIdEncuesta = document.querySelector('#survey_id_encuesta');
    const surveyQuestionsContainer = document.querySelector('#surveyQuestionsContainer');
    const surveyObservaciones = document.querySelector('#survey_observaciones');
    const btnEnviarEncuesta = document.querySelector('#btnEnviarEncuesta');

    //Estado local
    let documentosList = [];
    let categoriasList = [];
    let activeCategory = 0;

    //Cargar documentos del portal público
    const cargarPortal = async () => {
        documentsGrid.innerHTML = `
            <div class="loader-box">
                <div class="spinner"></div>
                <p>Consultando documentos...</p>
            </div>
        `;

        try {
            // El endpoint api/documentos/publicos.php retorna todos los documentos de acceso libre
            const res = await fetch('../../api/documentos/publicos.php', { method: 'GET' });
            const data = await res.json();

            if (!res.ok) {
                throw new Error(data.mensaje || 'Error al conectar con la base de datos.');
            }

            documentosList = data.documentos;
            renderizarDocumentos();

        } catch (error) {
            documentsGrid.innerHTML = `
                <div style="text-align:center; padding: 2rem; color:#ef4444;">
                    <strong>✕ Error:</strong> ${error.message}
                </div>
            `;
        }
    };

    // 2. Renderizar los documentos con filtros en el cliente 
    const renderizarDocumentos = () => {
        const busqueda = inputBuscar.value.toLowerCase().trim();

        const docsFiltrados = documentosList.filter((doc) => {
            const coincideBusqueda = doc.titulo.toLowerCase().includes(busqueda);
            const coincideCategoria = activeCategory === 0 || doc.id_categoria === activeCategory;
            return coincideBusqueda && coincideCategoria;
        });

        documentsGrid.innerHTML = '';

        if (docsFiltrados.length === 0) {
            documentsGrid.innerHTML = `
                <div style="text-align:center; padding: 3rem 1rem; color:#64748b; font-size:0.9rem;">
                    No hay documentos disponibles para su búsqueda en esta categoría.
                </div>
            `;
            return;
        }

        docsFiltrados.forEach((doc) => {
            const docCard = document.createElement('article');
            docCard.className = 'doc-card';

            

            // Todos los documentos desbloqueados de acceso libre
            const actionButton = `
                <a href="../../${doc.archivo}" target="_blank" class="btn-doc-download">
                    📄 Ver / Descargar (PDF)
                </a>
            `;

            docCard.innerHTML = `
                <div class="doc-info-top">
                    <span class="doc-badge-cat">${doc.categoria_nombre}</span>
                    
                </div>
                <h3>${doc.titulo}</h3>
                <div class="doc-card-actions">
                    ${actionButton}
                    <button class="btn-survey-trigger" data-categoria="${doc.id_categoria}" data-cat-nombre="${doc.categoria_nombre}">
                        ★ Responder Encuesta
                    </button>
                </div>
            `;

            documentsGrid.appendChild(docCard);
        });
    };

    //Cargar las categorías para las pestañas de filtro
    const cargarFiltrosCategorias = async () => {
        try {
            const res = await fetch('../../api/categorias/index.php', { method: 'GET' });
            const data = await res.json();

            if (res.ok && data.status === 'ok') {
                categoriasList = data.datos;
                
                categoriasList.forEach((cat) => {
                    const tabBtn = document.createElement('button');
                    tabBtn.className = 'tab-btn';
                    tabBtn.dataset.category = cat.id_categoria;
                    tabBtn.textContent = cat.nombre.split(' y ')[0]; //Nombre corto para celular
                    categoryTabs.appendChild(tabBtn);
                });
            }
        } catch (error) {
            console.error('Fallo al cargar categorías', error);
        }
    };

    //Filtros por categorías
    categoryTabs.addEventListener('click', (e) => {
        if (e.target.classList.contains('tab-btn')) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            e.target.classList.add('active');
            activeCategory = parseInt(e.target.dataset.category, 10);
            renderizarDocumentos();
        }
    });

    // Buscador interactivo
    inputBuscar.addEventListener('input', renderizarDocumentos);

    // LOGICA DE ENCUESTAS DE SATISFACCION DINÁMICAS
    
    // Objeto para almacenar las respuestas temporales de los botones de escala
    let respuestasEscala = {};

    const abrirEncuesta = async (idCategoria, catNombre) => {
        surveyQuestionsContainer.innerHTML = '<p class="text-center">Cargando preguntas de satisfacción...</p>';
        formEncuesta.classList.add('oculta');
        modalEncuesta.classList.remove('oculta');
        respuestasEscala = {}; //resetear escala

        try {
            const res = await fetch(`../../api/encuestas/index.php?id_categoria=${idCategoria}`);
            const data = await res.json();

            if (!res.ok) throw new Error(data.mensaje || 'Error al obtener la encuesta.');

            if (!data.encuesta) {
                surveyQuestionsContainer.innerHTML = `
                    <div style="text-align:center; padding: 1rem; color:#475569;">
                        No hay ninguna encuesta activa para el área: <strong>${catNombre}</strong> en este momento.
                    </div>
                `;
                btnCancelarEncuesta.textContent = "Volver";
                document.querySelector('#btnEnviarEncuesta').classList.add('oculta');
                return;
            }

            // Encuesta encontrada
            surveyTitle.textContent = `Servicio: ${catNombre}`;
            surveyIdEncuesta.value = data.encuesta.id_encuesta;
            btnCancelarEncuesta.textContent = "Cancelar";
            document.querySelector('#btnEnviarEncuesta').classList.remove('oculta');
            surveyQuestionsContainer.innerHTML = '';

            // Renderizar cada pregunta dinámicamente según su tipo
            data.encuesta.preguntas.forEach((pregunta, index) => {
                const block = document.createElement('div');
                block.className = 'question-block';
                block.dataset.id_pregunta = pregunta.id_pregunta;
                block.dataset.tipo = pregunta.tipo;

                const text = document.createElement('p');
                text.className = 'question-text';
                text.textContent = `${index + 1}. ${pregunta.enunciado}`;
                block.appendChild(text);

                // Renderizar inputs según el tipo de pregunta
                if (pregunta.tipo === 'Si/No') {
                    block.innerHTML += `
                        <div class="radio-options-row">
                            <label class="option-radio-label">
                                <input type="radio" name="q_${pregunta.id_pregunta}" value="Sí" required> Sí
                            </label>
                            <label class="option-radio-label">
                                <input type="radio" name="q_${pregunta.id_pregunta}" value="No" required> No
                            </label>
                        </div>
                    `;
                } else if (pregunta.tipo === 'Escala_1_5') {
                    // Contenedor de la escala
                    const scaleRow = document.createElement('div');
                    scaleRow.className = 'scale-options-row';
                    scaleRow.innerHTML = `
                        <span class="scale-label">Muy insatisfecho</span>
                        <div class="scale-items" data-pregunta="${pregunta.id_pregunta}">
                            <button type="button" class="scale-item-btn" data-val="1">1</button>
                            <button type="button" class="scale-item-btn" data-val="2">2</button>
                            <button type="button" class="scale-item-btn" data-val="3">3</button>
                            <button type="button" class="scale-item-btn" data-val="4">4</button>
                            <button type="button" class="scale-item-btn" data-val="5">5</button>
                        </div>
                        <span class="scale-label">Muy satisfecho</span>
                    `;
                    block.appendChild(scaleRow);
                } else if (pregunta.tipo === 'Texto Libre') {
                    block.innerHTML += `
                        <textarea name="q_${pregunta.id_pregunta}" rows="2" placeholder="Escriba aquí su respuesta..."></textarea>
                    `;
                }

                surveyQuestionsContainer.appendChild(block);
            });

            formEncuesta.classList.remove('oculta');

        } catch (error) {
            surveyQuestionsContainer.innerHTML = `
                <div style="text-align:center; padding: 1rem; color:#ef4444;">
                    Error al cargar encuesta: ${error.message}
                </div>
            `;
        }
    };

    // Manejar los clicks en el botón de encuestas
    documentsGrid.addEventListener('click', (e) => {
        if (e.target.classList.contains('btn-survey-trigger')) {
            const idCat = e.target.dataset.categoria;
            const catNombre = e.target.dataset.catNombre;
            abrirEncuesta(idCat, catNombre);
        }
    });

    // Escuchar selección de botones de la Escala 1-5 (Delegación de eventos)
    surveyQuestionsContainer.addEventListener('click', (e) => {
        if (e.target.classList.contains('scale-item-btn')) {
            const btn = e.target;
            const container = btn.parentElement;
            const idPregunta = container.dataset.pregunta;
            const val = btn.dataset.val;

            //Deseleccionar otros botones en el mismo contenedor
            container.querySelectorAll('.scale-item-btn').forEach(b => b.classList.remove('selected'));
            
            //Seleccionar el actual
            btn.classList.add('selected');
            
            //Guardar valor en el estado temporal
            respuestasEscala[idPregunta] = val;
        }
    });

    // Enviar encuesta
    formEncuesta.addEventListener('submit', async (e) => {
        e.preventDefault();

        const idEncuesta = parseInt(surveyIdEncuesta.value, 10);
        const observaciones = surveyObservaciones.value.trim();
        const respuestas = [];

        // Validar y recopilar las respuestas de las preguntas dinámicas
        const bloques = surveyQuestionsContainer.querySelectorAll('.question-block');
        let errorValidacion = false;

        bloques.forEach((bloque) => {
            const idPregunta = parseInt(bloque.dataset.id_pregunta, 10);
            const tipo = bloque.dataset.tipo;
            let respuestaValor = '';

            if (tipo === 'Si/No') {
                const seleccionado = bloque.querySelector(`input[name="q_${idPregunta}"]:checked`);
                if (!seleccionado) {
                    errorValidacion = true;
                } else {
                    respuestaValor = seleccionado.value;
                }
            } else if (tipo === 'Escala_1_5') {
                if (!respuestasEscala[idPregunta]) {
                    errorValidacion = true;
                } else {
                    respuestaValor = respuestasEscala[idPregunta];
                }
            } else if (tipo === 'Texto Libre') {
                const textarea = bloque.querySelector(`textarea[name="q_${idPregunta}"]`);
                respuestaValor = textarea.value.trim();
            }

            respuestas.push({
                id_pregunta: idPregunta,
                contenido_respuesta: respuestaValor
            });
        });

        if (errorValidacion) {
            alert('Por favor responda todas las preguntas con opción o calificación antes de enviar.');
            return;
        }

        btnEnviarEncuesta.disabled = true;
        btnEnviarEncuesta.textContent = 'Enviando...';

        try {
            const resSubmit = await fetch('../../api/encuestas/index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id_encuesta: idEncuesta,
                    observaciones: observaciones,
                    respuestas: respuestas
                })
            });

            const dataRes = await resSubmit.json();

            if (!resSubmit.ok) {
                throw new Error(dataRes.mensaje || 'Error al guardar respuestas.');
            }

            alert(dataRes.mensaje);
            modalEncuesta.classList.add('oculta');
            formEncuesta.reset();

        } catch (err) {
            alert('Error al enviar la encuesta: ' + err.message);
        } finally {
            btnEnviarEncuesta.disabled = false;
            btnEnviarEncuesta.textContent = 'Enviar Respuestas';
        }
    });

    // Control de cierre del modal de encuestas
    btnCerrarModalEncuesta.addEventListener('click', () => modalEncuesta.classList.add('oculta'));
    btnCancelarEncuesta.addEventListener('click', () => modalEncuesta.classList.add('oculta'));

    // Inicialización del portal
    cargarFiltrosCategorias();
    cargarPortal();
});
