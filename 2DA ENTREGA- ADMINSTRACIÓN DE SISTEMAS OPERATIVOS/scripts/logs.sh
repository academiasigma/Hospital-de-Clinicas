#!/bin/bash

verAccesosSsh() {
    echo ""
    echo "--- ACCESOS SSH (FECHA, HORA Y ESTADO) ---"
    grep -E "sshd.*(Accepted|Failed)" /var/log/secure 2>/dev/null | tail -n 20
    read -p "Presione ENTER para continuar."
}

verIniciosSesion() {
    echo ""
    echo "--- INICIOS Y CIERRES DE SESIÓN ---"
    grep -E "session (opened|closed)" /var/log/secure 2>/dev/null | tail -n 20
    read -p "Presione ENTER para continuar."
}

verComandosSudo() {
    echo ""
    echo "--- COMANDOS SUDO EJECUTADOS ---"
    grep "sudo:" /var/log/secure 2>/dev/null | tail -n 20
    read -p "Presione ENTER para continuar."
}

verJournalSsh() {
    echo ""
    echo "Mostrando logs de SSH en vivo (Presione Ctrl+C para salir)..."
    sleep 1
    journalctl -u sshd -n 20 -f
}

buscarPatronGrep() {
    echo ""
    echo "--- BÚSQUEDA POR PATRÓN (GREP) ---"
    read -p "Ingrese la palabra o término a buscar (ej: Failed, root, sigsm): " termino
    echo "Resultados coincidentes en /var/log/secure:"
    grep --color=always -i "$termino" /var/log/secure 2>/dev/null | tail -n 25
    read -p "Presione ENTER para continuar."
}

verLogCompleto() {
    less /var/log/secure
}

menuLogs() {
    opcLog=99
    while [ "$opcLog" -ne 0 ]; do
        clear
        echo "============================================="
        echo "       S.I.G.S.M. - AUDITORÍA Y LOGS         "
        echo "============================================="
        echo "1) Ver accesos SSH (Aceptados y Fallidos)"
        echo "2) Ver inicios y cierres de sesión"
        echo "3) Ver comandos ejecutados con sudo"
        echo "4) Monitoreo SSH en vivo (journalctl -u sshd)"
        echo "5) Buscar patrón con grep en /var/log/secure"
        echo "6) Ver /var/log/secure completo con less"
        echo "0) Volver al menú principal"
        echo "============================================="
        read -p "Seleccione una opción: " opcLog
        case $opcLog in
            1) verAccesosSsh ;;
            2) verIniciosSesion ;;
            3) verComandosSudo ;;
            4) verJournalSsh ;;
            5) buscarPatronGrep ;;
            6) verLogCompleto ;;
            0) echo "Volviendo..." ;;
            *) echo "Opción no válida." ;;
        esac
    done
}
