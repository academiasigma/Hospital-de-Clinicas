#!/bin/bash
#Gestión de los servicios esenciales!!

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
[ -f "$SCRIPT_DIR/comunes.sh" ] && source "$SCRIPT_DIR/comunes.sh"

# Lista de servicios esenciales del servidor hospitalario
SERVICIOS=("httpd" "sshd" "docker" "firewalld" "crond" "cockpit.socket")

# Muestra el estado resumido de todos los servicios esenciales
estado_servicios_general() {
    titulo "ESTADO DE SERVICIOS ESENCIALES DEL SISTEMA"
    echo -e "SERVICIO\t\tESTADO ACTIVO\t\tHABILITADO AL ARRANQUE"
    echo "-------------------------------------------------------------------"
    local s
    for s in "${SERVICIOS[@]}"; do
        local activo
        local habilitado
        activo=$(systemctl is-active "$s" 2>/dev/null)
        habilitado=$(systemctl is-enabled "$s" 2>/dev/null)
        printf "%-22s %-23s %s\n" "$s" "[$activo]" "[$habilitado]"
    done
    echo ""
    pausa
}

# Selector de servicio
seleccionar_servicio() {
    local s_sel=""
    echo "Servicios disponibles:"
    local i=1
    for s in "${SERVICIOS[@]}"; do
        echo " $i) $s"
        ((i++))
    done
    echo ""
    read -r -p "Seleccione el número de servicio: " num
    case "$num" in
        1) s_sel="httpd" ;;
        2) s_sel="sshd" ;;
        3) s_sel="docker" ;;
        4) s_sel="firewalld" ;;
        5) s_sel="crond" ;;
        6) s_sel="cockpit.socket" ;;
        *) s_sel="" ;;
    esac
    echo "$s_sel"
}

# Control del ciclo de vida (start, stop, restart)
control_demonio() {
    requiere_root || return 1
    local accion="$1"
    local nombre_accion="$2"

    titulo "SERVICIOS - $nombre_accion"
    local serv
    serv=$(seleccionar_servicio)

    if [ -z "$serv" ]; then
        mensaje_error "Selección inválida."
        pausa
        return 1
    fi

    echo "[*] Ejecutando: systemctl $accion $serv..."
    if systemctl "$accion" "$serv" &>/dev/null; then
        mensaje_ok "Servicio '$serv' $nombre_accion correctamente."
    else
        mensaje_error "Fallo al intentar $accion el servicio '$serv'."
    fi
    pausa
}

# Estado detallado de una unidad específica con journalctl/systemctl
ver_detalle_servicio() {
    titulo "DETALLE DE SERVICIO (STATUS)"
    local serv
    serv=$(seleccionar_servicio)

    if [ -z "$serv" ]; then
        mensaje_error "Selección inválida."
        pausa
        return 1
    fi

    echo ""
    systemctl status "$serv" --no-pager
    echo ""
    pausa
}

# Habilitar o deshabilitar arranque automático (enable/disable)
gestion_arranque() {
    requiere_root || return 1
    local accion="$1"
    local texto="$2"

    titulo "GESTIÓN DE ARRANQUE: $texto"
    local serv
    serv=$(seleccionar_servicio)

    if [ -z "$serv" ]; then
        mensaje_error "Selección inválida."
        pausa
        return 1
    fi

    echo "[*] Ejecutando: systemctl $accion $serv..."
    if systemctl "$accion" "$serv" &>/dev/null; then
        mensaje_ok "Servicio '$serv' configurado para $texto al iniciar el sistema."
    else
        mensaje_error "Fallo al ejecutar systemctl $accion sobre '$serv'."
    fi
    pausa
}

# Menú interactivo del módulo
menu_servicios() {
    local opcion=""
    while [ "$opcion" != "0" ]; do
        titulo "S.I.G.S.M. - GESTIÓN DE SERVICIOS ESENCIALES"
        echo " 1) Ver estado de todos los servicios esenciales"
        echo " 2) Ver estado detallado de un servicio (status)"
        echo " 3) Iniciar servicio (start)"
        echo " 4) Detener servicio (stop)"
        echo " 5) Reiniciar servicio (restart)"
        echo " 6) Habilitar al arranque del SO (enable)"
        echo " 7) Deshabilitar del arranque del SO (disable)"
        echo " 0) Volver al menú principal"
        echo "==================================================================="
        read -r -p "Seleccione una opción: " opcion

        case "$opcion" in
            1) estado_servicios_general ;;
            2) ver_detalle_servicio ;;
            3) control_demonio "start" "iniciado" ;;
            4) control_demonio "stop" "detenido" ;;
            5) control_demonio "restart" "reiniciado" ;;
            6) gestion_arranque "enable" "habilitar" ;;
            7) gestion_arranque "disable" "deshabilitar" ;;
            0) break ;;
            *) mensaje_error "Opción no válida"; pausa ;;
        esac
    done
}
