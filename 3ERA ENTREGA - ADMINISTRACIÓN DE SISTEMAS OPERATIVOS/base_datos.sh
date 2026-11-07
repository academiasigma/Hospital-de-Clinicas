#!/bin/bash
# Base de Datos (Mariadb en Docker)

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
[ -f "$SCRIPT_DIR/comunes.sh" ] && source "$SCRIPT_DIR/comunes.sh"

CONTENEDOR="sigsm_mariadb"

# Muestra el estado operativo del contenedor MariaDB
estado_mariadb() {
    titulo "ESTADO DE MARIADB (CONTENEDOR DOCKER)"
    if ! docker ps -a --filter "name=^/${CONTENEDOR}$" --format 'table {{.Names}}\t{{.Status}}\t{{.Ports}}' | grep -q "$CONTENEDOR"; then
        mensaje_error "El contenedor '$CONTENEDOR' no existe o Docker está apagado."
    else
        docker ps -a --filter "name=^/${CONTENEDOR}$" --format 'table {{.Names}}\t{{.Status}}\t{{.Ports}}'
    fi
    echo ""
    pausa
}

# Control del ciclo de vida del contenedor (start/stop/restart)
control_servicio_db() {
    requiere_root || return 1
    local accion="$1"
    local nombre_accion="$2"

    echo "[*] Ejecutando: docker $accion $CONTENEDOR..."
    docker "$accion" "$CONTENEDOR" &>/dev/null

    if [ $? -eq 0 ]; then
        mensaje_ok "Contenedor MariaDB $nombre_accion correctamente."
    else
        mensaje_error "Fallo al intentar $accion el contenedor MariaDB."
    fi
    pausa
}

# Acceso directo a la consola interactiva de MariaDB dentro del contenedor
consola_mariadb() {
    requiere_root || return 1
    titulo "CONSOLA ADMINISTRATIVA MARIADB (CLI)"
    
    # Comprueba si el contenedor está activo antes de intentar entrar
    if ! docker ps --filter "name=^/${CONTENEDOR}$" --format '{{.Names}}' | grep -q "$CONTENEDOR"; then
        mensaje_error "El contenedor '$CONTENEDOR' no está corriendo. Inícielo primero."
        pausa
        return 1
    fi

    echo "Iniciando consola de MariaDB dentro de Docker..."
    docker exec -it "$CONTENEDOR" mysql -u root -proot sigsm_hospital
}

# Menú interactivo del módulo
menu_base_datos() {
    local opcion=""
    while [ "$opcion" != "0" ]; do
        titulo "S.I.G.S.M. - MOTOR DE BASE DE DATOS (DOCKER)"
        echo " 1) Ver estado de MariaDB (docker ps)"
        echo " 2) Iniciar contenedor MariaDB"
        echo " 3) Detener contenedor MariaDB"
        echo " 4) Reiniciar contenedor MariaDB"
        echo " 5) Abrir consola interactiva (MySQL CLI)"
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
