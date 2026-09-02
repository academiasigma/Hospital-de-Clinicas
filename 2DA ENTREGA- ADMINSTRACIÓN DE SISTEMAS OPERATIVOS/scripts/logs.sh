#!/bin/bash
#¡¡Módulo de Logs!!

LOG_FILE="/var/log/secure"

ver_accesos_ssh() {
    requiere_root || return 1
    titulo "ACCESOS SSH (ACEPTADOS Y FALLIDOS)"
    if [ -f "$LOG_FILE" ]; then
        grep -E "sshd.*(Accepted|Failed)" "$LOG_FILE" | tail -n 25
    else
        mensaje_error "No se encontró el registro en $LOG_FILE."
    fi
    echo ""
    pausa
}

ver_inicios_sesion() {
    requiere_root || return 1
    titulo "INICIOS Y CIERRES DE SESIÓN"
    if [ -f "$LOG_FILE" ]; then
        grep -E "session opened|session closed" "$LOG_FILE" | tail -n 25
    else
        mensaje_error "No se encontró el archivo $LOG_FILE."
    fi
    echo ""
    pausa
}

ver_comandos_sudo() {
    requiere_root || return 1
    titulo "COMANDOS EJECUTADOS CON SUDO"
    if [ -f "$LOG_FILE" ]; then
        grep "sudo:" "$LOG_FILE" | grep "COMMAND=" | tail -n 25
    else
        mensaje_error "No se encontró el archivo $LOG_FILE."
    fi
    echo ""
    pausa
}

ver_journal_ssh() {
    requiere_root || return 1
    echo "--- MONITOREO SSH EN TIEMPO REAL (Ctrl + C para salir) ---"
    sleep 1
    journalctl -u sshd -f
}

buscar_patron_grep() {
    requiere_root || return 1
    local patron=""
    echo "--- BUSCAR EN $LOG_FILE ---"
    read -r -p "Ingrese palabra o patrón a buscar: " patron

    if [ -z "$patron" ]; then
        mensaje_error "El patrón de búsqueda no puede estar vacío."
        pausa
        return 1
    fi

    if [ -f "$LOG_FILE" ]; then
        grep --color=auto -i "$patron" "$LOG_FILE" | tail -n 30
    else
        mensaje_error "No se encontró el archivo $LOG_FILE."
    fi
    echo ""
    pausa
}

ver_log_completo() {
    requiere_root || return 1
    if [ -f "$LOG_FILE" ]; then
        less "$LOG_FILE"
    else
        mensaje_error "No se encontró el archivo $LOG_FILE."
        pausa
    fi
}

menu_logs() {
    local opcion=""
    while [ "$opcion" != "0" ]; do
        titulo "S.I.G.S.M. - AUDITORÍA Y LOGS (/var/log/secure)"
        echo " 1) Ver accesos SSH (Aceptados y Fallidos)"
        echo " 2) Ver inicios y cierres de sesión"
        echo " 3) Ver comandos ejecutados con sudo"
        echo " 4) Monitoreo SSH en vivo (journalctl -u sshd)"
        echo " 5) Buscar patrón con grep en /var/log/secure"
        echo " 6) Ver /var/log/secure completo con less"
        echo " 0) Volver al menú principal"
        echo "==================================================================="
        read -r -p "Seleccione una opción: " opcion

        case "$opcion" in
            1) ver_accesos_ssh ;;
            2) ver_inicios_sesion ;;
            3) ver_comandos_sudo ;;
            4) ver_journal_ssh ;;
            5) buscar_patron_grep ;;
            6) ver_log_completo ;;
            0) break ;;
            *) mensaje_error "Opción no válida"; pausa ;;
        esac
    done
}
