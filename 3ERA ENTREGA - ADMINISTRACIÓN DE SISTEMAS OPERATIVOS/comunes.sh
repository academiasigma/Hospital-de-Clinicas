#!/bin/bash
#¡¡Funciones auxiliares!!

mensaje_error() {
    echo "[X] $1"
}

mensaje_ok() {
    echo "[OK] $1"
}

titulo() {
    clear
    echo "==================================================================="
    echo "  $1"
    echo "==================================================================="
    echo ""
}

pausa() {
    read -r -p "Presione ENTER para continuar..."
}

requiere_root() {
    if [ "$EUID" -ne 0 ]; then
        mensaje_error "Esta operación requiere permisos de administrador (ejecute con sudo o como root)."
        return 1
    fi
    return 0
}

usuario_existe() {
    local usuario="$1"
    getent passwd "$usuario" &>/dev/null
}

grupo_existe() {
    local grupo="$1"
    getent group "$grupo" &>/dev/null
}
