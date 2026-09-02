
document.addEventListener('DOMContentLoaded', () => {
    // Selectores
    const selectChoferActivo = document.querySelector('#selectChoferActivo');
    const sinViajeBox = document.querySelector('#sinViajeBox');
    const viajeAsignadoBox = document.querySelector('#viajeAsignadoBox');

    const txtViajeId = document.querySelector('#txtViajeId');
    const badgePrioridadChofer = document.querySelector('#badgePrioridadChofer');
    const txtViajeOrigen = document.querySelector('#txtViajeOrigen');
    const txtViajeDestino = document.querySelector('#txtViajeDestino');
    const txtViajePaciente = document.querySelector('#txtViajePaciente');
    const txtViajeVehiculo = document.querySelector('#txtViajeVehiculo');
    const txtViajeEnfermero = document.querySelector('#txtViajeEnfermero');
    const txtViajeEstado = document.querySelector('#txtViajeEstado');

    // Acciones y contenedores de botones
    const actionsPendiente = document.querySelector('#actionsPendiente');
    const actionsEnCurso = document.querySelector('#actionsEnCurso');
    const btnIniciarViaje = document.querySelector('#btnIniciarViaje');
    const btnFinalizarViaje = document.querySelector('#btnFinalizarViaje');

    let traslados = [];
    let choferActivo = selectChoferActivo.value;
    let viajeActivo = null;

    // Obtener los datos actuales del localStorage
    const cargarDatos = () => {
        const localData = localStorage.getItem('sigsm_traslados_simulados');
        if (localData) {
            traslados = JSON.parse(localData);
        } else {
            traslados = [];
        }
    };

    const guardarDatos = () => {
        localStorage.setItem('sigsm_traslados_simulados', JSON.stringify(traslados));
    };

    // Buscar y renderizar el viaje del chofer seleccionado
    const buscarViajeAsignado = () => {
        cargarDatos();
        choferActivo = selectChoferActivo.value;

        // Buscamos un viaje para este chofer que esté 'Pendiente' o 'En Curso'.
        // Priorizamos el viaje 'En Curso' si existe, sino el 'Pendiente' más viejo.
        viajeActivo = traslados.find(t => t.chofer === choferActivo && t.estado === 'En Curso');
        if (!viajeActivo) {
            viajeActivo = traslados.find(t => t.chofer === choferActivo && t.estado === 'Pendiente');
        }

        if (!viajeActivo) {
            sinViajeBox.classList.remove('oculta');
            viajeAsignadoBox.classList.add('oculta');
            return;
        }

        // Si hay viaje activo, rellenar datos
        sinViajeBox.classList.add('oculta');
        viajeAsignadoBox.classList.remove('oculta');

        txtViajeId.textContent = `#${viajeActivo.id}`;
        txtViajeOrigen.textContent = viajeActivo.origen;
        txtViajeDestino.textContent = viajeActivo.destino;
        txtViajePaciente.textContent = viajeActivo.tipo_traslado === 'objeto'
            ? `${viajeActivo.paciente} (Objeto)`
            : `${viajeActivo.paciente}`;
        txtViajeVehiculo.textContent = viajeActivo.vehiculo;
        txtViajeEnfermero.textContent = viajeActivo.enfermero;

        // Renderizar Prioridad
        badgePrioridadChofer.textContent = viajeActivo.prioridad.toUpperCase();
        badgePrioridadChofer.className = 'badge-prioridad';
        if (viajeActivo.prioridad === 'Alta') {
            badgePrioridadChofer.classList.add('badge-prio-alta');
        } else if (viajeActivo.prioridad === 'Media') {
            badgePrioridadChofer.classList.add('badge-prio-media');
        } else {
            badgePrioridadChofer.classList.add('badge-prio-baja');
        }

        // Renderizar Estado
        txtViajeEstado.textContent = viajeActivo.estado;
        txtViajeEstado.className = 'badge-estado';
        if (viajeActivo.estado === 'Pendiente') {
            txtViajeEstado.classList.add('status-yellow');
            actionsPendiente.classList.remove('oculta');
            actionsEnCurso.classList.add('oculta');
        } else {
            txtViajeEstado.classList.add('status-blue');
            actionsPendiente.classList.add('oculta');
            actionsEnCurso.classList.remove('oculta');
        }
    };

    // Cambiar chofer activo
    selectChoferActivo.addEventListener('change', buscarViajeAsignado);

    // Iniciar el traslado
    btnIniciarViaje.addEventListener('click', () => {
        if (!viajeActivo) return;

        traslados = traslados.map(t => {
            if (t.id === viajeActivo.id) {
                return { ...t, estado: 'En Curso' };
            }
            return t;
        });

        guardarDatos();
        buscarViajeAsignado();
    });

    // Finalizar el traslado
    btnFinalizarViaje.addEventListener('click', () => {
        if (!viajeActivo) return;

        traslados = traslados.map(t => {
            if (t.id === viajeActivo.id) {
                return { ...t, estado: 'Completado', alerta: '' };
            }
            return t;
        });

        guardarDatos();
        buscarViajeAsignado();
        alert('¡Traslado completado con éxito! Registro sincronizado y archivado.');
    });

    // Lanzar alerta de tránsito
    actionsEnCurso.addEventListener('click', (e) => {
        if (e.target.classList.contains('btn-mobile-alert')) {
            if (!viajeActivo) return;

            const tipoAlerta = e.target.dataset.alerta;

            traslados = traslados.map(t => {
                if (t.id === viajeActivo.id) {
                    return { ...t, alerta: tipoAlerta };
                }
                return t;
            });

            guardarDatos();
            alert(`⚠️ Alerta enviada al despacho del hospital: "${tipoAlerta}"`);
        }
    });

    // Escuchar actualizaciones del localStorage de otras pestañas (Despacho admin)
    window.addEventListener('storage', (e) => {
        if (e.key === 'sigsm_traslados_simulados') {
            buscarViajeAsignado();
        }
    });

    // Carga inicial
    buscarViajeAsignado();
});
