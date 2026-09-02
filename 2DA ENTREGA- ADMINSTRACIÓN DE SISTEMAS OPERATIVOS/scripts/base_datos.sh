#!/bin/bash
#¡¡Módulo del motor de bd (MariaDB)

estado_mariadb() {
    titulo "ESTADO DE MARIADB"
    systemctl status mariadb --no-pager
    echo ""
    pausa
}

control_servicio_db() {
    requiere_root || return 1
    local accion="$1"
    local nombre_accion="$2"

    echo "[*] Ejecutando: systemctl $accion mariadb..."
    systemctl "$accion" mariadb

    if [ $? -eq 0 ]; then
        mensaje_ok "Servicio MariaDB $nombre_accion correctamente."
    else
        mensaje_error "Fallo al intentar $accion MariaDB."
    fi
    pausa
}

consola_mariadb() {
    requiere_root || return 1
    echo "Iniciando consola interactiva de MariaDB (MySQL CLI)..."
    mysql -u root -p
}

menu_base_datos() {
    local opcion=""
    while [ "$opcion" != "0" ]; do
        titulo "S.I.G.S.M. - MOTOR DE BASE DE DATOS"
        echo " 1) Ver estado de MariaDB (systemctl status)"
        echo " 2) Iniciar servicio MariaDB"
        echo " 3) Detener servicio MariaDB"
        echo " 4) Reiniciar servicio MariaDB"
        echo " 5) Abrir consola administrativa (MySQL CLI)"
        echo " 0) Volver al menú principal"
        echo "==================================================================="
        read -r -p "Seleccione una opción: " opcion

        case "$opcion" in
            1) estado_mariadb ;;
            2) control_servicio_db "start" "iniciado" ;;
            3) control_servicio_db "stop" "detenido" ;;
            4) control_servicio_db "restart" "reiniciado" ;;
            5) consola_mariadb ;;
            0) break ;;
            *) mensaje_error "Opción no válida"; pausa ;;
        esac
    done
}
