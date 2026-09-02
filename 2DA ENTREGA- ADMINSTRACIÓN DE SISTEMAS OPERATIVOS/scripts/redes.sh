#!/bin/bash
# ¡¡Módulo de menu de redes!!

menu_redes() {
    requiere_root || return 1
    titulo "S.I.G.S.M. - CONFIGURACIÓN DE RED (NMTUI)"
    
    if ! command -v nmtui &>/dev/null; then
        mensaje_error "La utilidad 'nmtui' no está instalada en este sistema."
        pausa
        return 1
    fi

    echo "Iniciando la herramienta oficial NMTUI..."
    pausa
    nmtui
    mensaje_ok "Sesión de NMTUI concluida."
    pausa
}
