#!/bin/bash
# ==============================================================================
# S.I.G.S.M. - Panel de Control del Operador de Centro de Cómputos
# Sistema Informático de Gestión de Servicios Médicos - Hospital de Clínicas
# ==============================================================================

# Verificación de superusuario
if [ "$EUID" -ne 0 ]; then
    echo "Error: Este script debe ejecutarse con privilegios de root (use sudo o su -)."
    exit 1
fi

# Importación de los módulos secundarios (igual a como se dio en clase)
source "$SCRIPT_DIR./usuarios.sh"
source "$SCRIPT_DIR./respaldo.sh"
source "$CRIPT_DIR./redes.sh"
source "$CRIPT_DIR./base_datos.sh"
source "$CRIPT_DIR./firewall.sh"
source "$CRIPT_DIR./logs.sh"

opcPrincipal=99
while [ "$opcPrincipal" -ne 0 ]; do
    clear
    echo "==================================================================="
    echo "         S.I.G.S.M. - CENTRO DE CÓMPUTOS (HOSPITAL DE CLÍNICAS)    "
    echo "==================================================================="
    echo " 1) Gestión de Usuarios y Grupos"
    echo " 2) Gestión de Respaldos (SCP y CRON)"
    echo " 3) Configuración de Redes (NMTUI)"
    echo " 4) Gestión de Base de Datos (MariaDB)"
    echo " 5) Cortafuegos (Firewalld)"
    echo " 6) Auditoría y Logs del Sistema (/var/log/secure)"
    echo " 0) Salir"
    echo "==================================================================="
    read -p "Seleccione una opción: " opcPrincipal

    case $opcPrincipal in
        1) menuUsuarios ;;
        2) menuRespaldos ;;
        3) menuRedes ;;
        4) menuBaseDatos ;;
        5) menuFirewall ;;
        6) menuLogs ;;
        0)
            echo "Saliendo del sistema de administración S.I.G.S.M..."
            exit 0
            ;;
        *)
            echo "Opción no válida."
            read -p "Presione ENTER para continuar."
            ;;
    esac
done
