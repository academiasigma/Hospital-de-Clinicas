document.addEventListener('DOMContentLoaded', () => {
    // Selectores
    const formAltaTraslado = document.querySelector('#formAltaTraslado');
    const tipoTrasladoSelect = document.querySelector('#tipo_traslado');
    const grupoPaciente = document.querySelector('#grupo_paciente');
    const grupoObjeto = document.querySelector('#grupo_objeto');
    const grupoEnfermero = document.querySelector('#grupo_enfermero');
    
    const inputObjeto = document.querySelector('#objeto');
    const inputPaciente = document.querySelector('#paciente');
    const selectEnfermero = document.querySelector('#enfermero');
    const selectOrigen = document.querySelector('#origen');
    const selectDestino = document.querySelector('#destino');
    
    const grupoOrigenOtro = document.querySelector('#grupo_origen_otro');
    const inputOrigenOtro = document.querySelector('#origen_otro');
    const grupoDestinoOtro = document.querySelector('#grupo_destino_otro');
    const inputDestinoOtro = document.querySelector('#destino_otro');

    const tablaTrasladosCuerpo = document.querySelector('#tablaTrasladosCuerpo');
    const btnLimpiarSimulacion = document.querySelector('#btnLimpiarSimulacion');

    // Selectores Estadísticas
    const statTotalVal = document.querySelector('#statTotalVal');
    const statPendientesVal = document.querySelector('#statPendientesVal');
    const statEnCursoVal = document.querySelector('#statEnCursoVal');
    const statCompletadosVal = document.querySelector('#statCompletadosVal');

    // Obtener y iniciar la lista de traslados simulados
    const obtenerTraslados = () => {
        // Traslados por default iniciales
        const defaultTraslados = [
            {
                id: 1,
                tipo_traslado: 'paciente',
                paciente: 'Facundo Guedikian',
                origen: 'Piso 3 - Sala 302',
                destino: 'Sala de Tomografía (Piso 1)',
                vehiculo: 'Móvil 101 (Soporte Básico - Matrícula HCA-1020)',
                chofer: 'Carlos Rodríguez',
                enfermero: 'Ana Martínez (Habilitada)',
                prioridad: 'Alta',
                estado: 'En Curso',
                alerta: 'Tránsito Congestionado'
            },
            {
                id: 2,
                tipo_traslado: 'paciente',
                paciente: 'Ihojan Robaina',
                origen: 'Puerta de Emergencias',
                destino: 'CTI de Adultos - Piso 5',
                vehiculo: 'Móvil 102 (Soporte Avanzado - Matrícula HCB-3040)',
                chofer: 'Mario Gómez',
                enfermero: 'No requiere',
                prioridad: 'Media',
                estado: 'Pendiente',
                alerta: ''
            }
        ];

        const localData = localStorage.getItem('sigsm_traslados_simulados');
        if (localData) {
            try {
                const parsed = JSON.parse(localData);
                // Si la simulación está vacía o es obsoleta (no contiene Facundo Guedikian), forzamos reinicio para que vean los ejemplos.
                if (!Array.isArray(parsed) || parsed.length === 0 || !parsed.some(t => t.paciente && t.paciente.includes('Guedikian'))) {
                    localStorage.setItem('sigsm_traslados_simulados', JSON.stringify(defaultTraslados));
                    return defaultTraslados;
                }
                return parsed;
            } catch (e) {
                localStorage.setItem('sigsm_traslados_simulados', JSON.stringify(defaultTraslados));
                return defaultTraslados;
            }
        }
        localStorage.setItem('sigsm_traslados_simulados', JSON.stringify(defaultTraslados));
        return defaultTraslados;
    };

    let traslados = obtenerTraslados();

    // Manejar el cambio de Tipo de Traslado (Paciente o Objeto)
    if (tipoTrasladoSelect) {
        tipoTrasladoSelect.addEventListener('change', () => {
            if (tipoTrasladoSelect.value === 'paciente') {
                grupoPaciente.classList.remove('oculta');
                grupoObjeto.classList.add('oculta');
                grupoEnfermero.classList.remove('oculta');
                
                inputObjeto.required = false;
                inputPaciente.required = true;
                selectEnfermero.required = true;
                
                inputObjeto.value = '';
                selectEnfermero.value = ''; // Exigir seleccionar sí o sí uno
            } else {
                // Si es Objeto (Órgano / Material Biológico)
                grupoPaciente.classList.add('oculta');
                grupoObjeto.classList.remove('oculta');
                grupoEnfermero.classList.add('oculta'); // No requiere enfermero clínico
                
                inputObjeto.required = true;
                inputPaciente.required = false;
                selectEnfermero.required = false;
                
                inputPaciente.value = '';
                selectEnfermero.value = 'No requiere'; // Precompletar automáticamente
            }
        });
    }

    // Manejar inputs personalizados "Otro (Especificar)" para Origen
    if (selectOrigen) {
        selectOrigen.addEventListener('change', () => {
            if (selectOrigen.value === 'OTRO') {
                grupoOrigenOtro.classList.remove('oculta');
                inputOrigenOtro.required = true;
                inputOrigenOtro.focus();
            } else {
                grupoOrigenOtro.classList.add('oculta');
                inputOrigenOtro.required = false;
                inputOrigenOtro.value = '';
            }
        });
    }

    // Manejar inputs personalizados "Otro (Especificar)" para Destino
    if (selectDestino) {
        selectDestino.addEventListener('change', () => {
            if (selectDestino.value === 'OTRO') {
                grupoDestinoOtro.classList.remove('oculta');
                inputDestinoOtro.required = true;
                inputDestinoOtro.focus();
            } else {
                grupoDestinoOtro.classList.add('oculta');
                inputDestinoOtro.required = false;
                inputDestinoOtro.value = '';
            }
        });
    }

    const guardarTraslados = () => {
        localStorage.setItem('sigsm_traslados_simulados', JSON.stringify(traslados));
        actualizarEstadisticas();
        renderizarTabla();
    };

    const actualizarEstadisticas = () => {
        const total = traslados.length;
        const pendientes = traslados.filter(t => t.estado === 'Pendiente').length;
        const enCurso = traslados.filter(t => t.estado === 'En Curso').length;
        const completados = traslados.filter(t => t.estado === 'Completado').length;

        statTotalVal.textContent = total;
        statPendientesVal.textContent = pendientes;
        statEnCursoVal.textContent = enCurso;
        statCompletadosVal.textContent = completados;
    };

    const renderizarTabla = () => {
        tablaTrasladosCuerpo.innerHTML = '';

        if (traslados.length === 0) {
            tablaTrasladosCuerpo.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        No hay traslados activos registrados en el sistema. Use el formulario de la derecha para despachar uno nuevo.
                    </td>
                </tr>
            `;
            return;
        }

        traslados.forEach((t) => {
            const tr = document.createElement('tr');

            // Badge de prioridad
            let badgePrioridad = '';
            if (t.prioridad === 'Alta') {
                badgePrioridad = '<span class="badge-prio-alta">Alta</span>';
            } else if (t.prioridad === 'Media') {
                badgePrioridad = '<span class="badge-prio-media">Media</span>';
            } else {
                badgePrioridad = '<span class="badge-prio-baja">Baja</span>';
            }

            // Badge de estado
            let badgeEstado = '';
            if (t.estado === 'Pendiente') {
                badgeEstado = '<span class="status-badge status-yellow">Pendiente</span>';
            } else if (t.estado === 'En Curso') {
                badgeEstado = '<span class="status-badge status-blue pulse-badge">En Curso</span>';
            } else if (t.estado === 'Completado') {
                badgeEstado = '<span class="status-badge status-green">Completado</span>';
            } else {
                badgeEstado = '<span class="status-badge status-red">Cancelado</span>';
            }

            // Alerta
            const alertaHTML = t.alerta 
                ? `<span class="alert-badge-table blink-alert">⚠️ ${t.alerta}</span>` 
                : '<span class="text-muted" style="font-size:0.8rem;">Sin Incidencias</span>';

            // Botones de acción
            let btnAcciones = '';
            if (t.estado === 'Pendiente' || t.estado === 'En Curso') {
                btnAcciones = `
                    <button class="btn-action btn-delete btn-cancelar-traslado" data-id="${t.id}">
                        Cancelar
                    </button>
                `;
            } else {
                btnAcciones = `<span class="text-muted" style="font-size: 0.8rem;">Ninguna</span>`;
            }

            // Determinar nombre a mostrar (paciente o objeto)
            const displayNombre = t.tipo_traslado === 'objeto' 
                ? `${t.paciente} (Objeto)` 
                : `${t.paciente}`;

            tr.innerHTML = `
                <td><strong>#${t.id}</strong></td>
                <td><strong>${displayNombre}</strong></td>
                <td>
                    <div style="font-size:0.85rem;">
                        <span style="color:#16a34a;">Desde: ${t.origen}</span><br>
                        <span style="color:#dc2626;">Hacia: ${t.destino}</span>
                    </div>
                </td>
                <td>
                    <div style="font-size:0.75rem; color:#475569;">
                        <strong>Móvil:</strong> ${t.vehiculo.split(' (')[0]}<br>
                        <strong>Chofer:</strong> ${t.chofer}<br>
                        <strong>Enf:</strong> ${t.enfermero.split(' (')[0]}
                    </div>
                </td>
                <td>${badgePrioridad}</td>
                <td>${badgeEstado}</td>
                <td>${alertaHTML}</td>
                <td>${btnAcciones}</td>
            `;

            tablaTrasladosCuerpo.appendChild(tr);
        });
    };

    // Crear un nuevo traslado
    formAltaTraslado.addEventListener('submit', (e) => {
        e.preventDefault();

        const tipoTraslado = tipoTrasladoSelect.value;
        let paciente = '';
        if (tipoTraslado === 'paciente') {
            paciente = inputPaciente.value;
        } else {
            paciente = inputObjeto.value;
        }
        
        // Resolver Origen (común o otro personalizado)
        let origen = selectOrigen.value;
        if (origen === 'OTRO') {
            origen = inputOrigenOtro.value.trim();
            if (!origen) {
                alert('Por favor, escriba el origen personalizado.');
                inputOrigenOtro.focus();
                return;
            }
        }

        // Resolver Destino (común o otro personalizado)
        let destino = selectDestino.value;
        if (destino === 'OTRO') {
            destino = inputDestinoOtro.value.trim();
            if (!destino) {
                alert('Por favor, escriba el destino personalizado.');
                inputDestinoOtro.focus();
                return;
            }
        }

        const vehiculo = document.querySelector('#vehiculo').value;
        const chofer = document.querySelector('#chofer').value;
        const enfermero = selectEnfermero.value;
        const prioridad = document.querySelector('#prioridad').value;

        if (!paciente || !origen || !destino || !vehiculo || !chofer || !enfermero) {
            alert('Por favor, complete todos los campos obligatorios (*).');
            return;
        }

        //Regla de Seguridad Clínica: Si es prioridad Alta y es Paciente, requiere enfermero acompañante
        if (prioridad === 'Alta' && tipoTraslado === 'paciente' && enfermero === 'No requiere') {
            alert('⚠️ Regla de Seguridad Clínica: Los traslados de pacientes de prioridad ALTA requieren obligatoriamente de un Enfermero Acompañante para la asistencia en viaje.');
            return;
        }

        const nuevoId = traslados.length > 0 ? Math.max(...traslados.map(t => t.id)) + 1 : 1;

        const nuevoTraslado = {
            id: nuevoId,
            tipo_traslado: tipoTraslado,
            paciente: paciente,
            origen: origen,
            destino: destino,
            vehiculo: vehiculo,
            chofer: chofer,
            enfermero: enfermero,
            prioridad: prioridad,
            estado: 'Pendiente',
            alerta: ''
        };

        traslados.unshift(nuevoTraslado); //Agregar al inicio de la lista
        guardarTraslados();
        formAltaTraslado.reset();
        
        // Ocultar campos de "Otro" despúes de resetear
        grupoOrigenOtro.classList.add('oculta');
        inputOrigenOtro.required = false;
        grupoDestinoOtro.classList.add('oculta');
        inputDestinoOtro.required = false;
        
        // Resetear visibilidad de enfermero/objeto
        grupoPaciente.classList.remove('oculta');
        grupoObjeto.classList.add('oculta');
        grupoEnfermero.classList.remove('oculta');
        inputPaciente.required = true;
        inputObjeto.required = false;

        alert('¡Unidad Despachada Exitosamente! El chofer asignado ya puede visualizar el traslado en su terminal.');
    });

    // Cancelar un traslado
    tablaTrasladosCuerpo.addEventListener('click', (e) => {
        if (e.target.classList.contains('btn-cancelar-traslado')) {
            const id = parseInt(e.target.dataset.id, 10);
            if (confirm(`¿Confirma la cancelación del traslado #${id}?`)) {
                traslados = traslados.map(t => t.id === id ? { ...t, estado: 'Cancelado', alerta: '' } : t);
                guardarTraslados();
            }
        }
    });

    // Botón para limpiar y restablecer la simulación
    btnLimpiarSimulacion.addEventListener('click', () => {
        if (confirm('¿Desea restablecer todos los viajes simulados a sus valores iniciales?')) {
            localStorage.removeItem('sigsm_traslados_simulados');
            traslados = obtenerTraslados();
            guardarTraslados();
        }
    });

    // Escuchar cambios del localStorage en tiempo real para actualizar la tabla (si se modifica desde la vista del chofer)
    window.addEventListener('storage', (e) => {
        if (e.key === 'sigsm_traslados_simulados') {
            traslados = JSON.parse(e.newValue);
            actualizarEstadisticas();
            renderizarTabla();
        }
    });

    // Cargar iniciales
    actualizarEstadisticas();
    renderizarTabla();
});
