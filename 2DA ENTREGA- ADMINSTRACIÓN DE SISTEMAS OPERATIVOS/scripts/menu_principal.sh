#!/bin/bash

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

if [ -f "$SCRIPT_DIR/comunes.sh" ]; then
    source "$SCRIPT_DIR/comunes.sh"
else
    echo "Error crítico: No se encontró el archivo $SCRIPT_DIR/comunes.sh"
    exit 1
fi

requiere_root || exit 1

source "$SCRIPT_DIR/usuarios.sh"
source "$SCRIPT_DIR/respaldo.sh"
source "$SCRIPT_DIR/redes.sh"
source "$SCRIPT_DIR/base_datos.sh"
source "$SCRIPT_DIR/firewall.sh"
source "$SCRIPT_DIR/logs.sh"

opcion=""

while [ "$opcion" != "0" ]; do
    titulo "S.I.G.S.M. - CENTRO DE CÓMPUTOS (HOSPITAL DE CLÍNICAS)"
    echo " 1) Gestión de Usuarios y Grupos"
    echo " 2) Gestión de Respaldos (SCP y CRON)"
    echo " 3) Configuración de Redes (NMTUI)"
    echo " 4) Gestión de Base de Datos (MariaDB)"
    echo " 5) Cortafuegos (Firewalld)"
    echo " 6) Auditoría y Logs del Sistema (/var/log/secure)"
    echo " 0) Salir"
    echo "==================================================================="
    read -r -p "Seleccione una opción: " opcion

    case "$opcion" in
        1) menu_usuarios ;;
        2) menu_respaldos ;;
        3) menu_redes ;;
        4) menu_base_datos ;;
        5) menu_firewall ;;
        6) menu_logs ;;
        0) echo "Cerrando panel de administración..."; break ;;
        *) mensaje_error "No es una opción válida"; pausa ;;
    esac
done

