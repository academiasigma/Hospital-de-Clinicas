function registrarTraslado(){ 

    // tomamos los campos que pide el proyecto
    let origen = document.getElementById("origen").value;
    let destino = document.getElementById("destino").value;
    let chofer = document.getElementById("chofer").value;
    let enfermero = document.getElementById("enfermero").value;
    let paciente = document.getElementById("paciente").value;
    let horaSalida = document.getElementById("horaSalida").value;
    let horaLlegada = document.getElementById("horaLlegada").value;

    if(!origen || !destino || !chofer || !enfermero || !paciente){
        alert("Por favor, complete todos los campos obligatorios.");
        return;
    }
    
    document.getElementById("tablaBody").innerHTML +=
    `<tr>
        <td>${paciente}</td>
        <td>De: ${origen} a ${destino} <br> <small>Salida: ${horaSalida || 'N/A'} - Llegada: ${horaLlegada || 'N/A'}</small></td>
        <td>Chofer: ${chofer} <br> Enf: ${enfermero}</td>
        <td><span class="badge bg-warning">En curso</span></td>
    </tr>`;

    // limpiar campos para el prox registro
    document.getElementById("origen").value = "";
    document.getElementById("destino").value = "";
    document.getElementById("chofer").value = "";
    document.getElementById("enfermero").value = "";
    document.getElementById("paciente").value = "";
    document.getElementById("horaSalida").value = "";
    document.getElementById("horaLlegada").value = "";
}

function enviarEncuesta(){
    let comentario = document.getElementById("comentario").value;
    
    if(!comentario){
        alert("Por favor, ingrese un comentario.");
        return;
    }
    
    // muestra la encuestas anónima
    document.getElementById("encuestas").innerHTML += `<li class="list-group-item"><strong>Anónimo:</strong> ${comentario}</li>`;

    document.getElementById("comentario").value = "";
}