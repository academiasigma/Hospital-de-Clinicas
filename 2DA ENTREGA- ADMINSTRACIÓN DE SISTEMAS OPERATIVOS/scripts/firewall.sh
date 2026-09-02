#!/bin/bash
#Módulo de Firewalld

ver_reglas_firewall() {
    titulo "ESTADO DE FIREWALLD"
    echo "--- Zonas Activas ---"
    firewall-cmd --get-active-zones
    echo ""
    echo "--- Reglas de la Zona por Defecto ---"
    firewall-cmd --list-all
    echo ""
    pausa
}

gestionar_puertos() {
    requiere_root || return 1
    local puerto=""
    local accion=""
    local tipo=""
    local mod_perm=""
    local flag_cmd=""

    echo "--- GESTIÓN DE PUERTOS ---"
    read -r -p "Ingrese puerto y protocolo (ej: 8080/tcp): " puerto
    if [ -z "$puerto" ]; then
        mensaje_error "El puerto no puede estar vacío."
        pausa
        return 1
    fi

    read -r -p "¿Desea (A)brir o (C)errar el puerto? (a/c): " accion
    read -r -p "¿Aplicar de forma (P)ermanente o (T)emporal? (p/t): " tipo

    [ "$tipo" = "p" ] || [ "$tipo" = "P" ] && mod_perm="--permanent"

    case "$accion" in
        a|A) flag_cmd="--add-port=$puerto" ;;
        c|C) flag_cmd="--remove-port=$puerto" ;;
        *)
            mensaje_error "Acción no válida."
            pausa
            return 1
            ;;
    esac

    firewall-cmd $mod_perm "$flag_cmd" &>/dev/null
    if [ $? -eq 0 ]; then
        [ -n "$mod_perm" ] && firewall-cmd --reload &>/dev/null
        mensaje_ok "Regla sobre el puerto '$puerto' aplicada correctamente."
    else
        mensaje_error "No se pudo aplicar la regla para el puerto '$puerto'."
    fi
    pausa
}

bloquear_ip() {
    requiere_root || return 1
    local ip=""
    local accion=""
    local tipo=""
    local mod_perm=""
    local regla=""

    echo "--- FILTRADO POR IP O SUBRED ---"
    read -r -p "Ingrese la IP o Red (ej: 192.168.1.50): " ip
    if [ -z "$ip" ]; then
        mensaje_error "Debe indicar una dirección IP o subred válida."
        pausa
        return 1
    fi

    read -r -p "¿Desea (B)loquear (drop) o (P)ermitir (accept)? (b/p): " accion
    read -r -p "¿Aplicar de forma (P)ermanente o (T)emporal? (p/t): " tipo

    [ "$tipo" = "p" ] || [ "$tipo" = "P" ] && mod_perm="--permanent"

    case "$accion" in
        b|B) regla="rule family=\"ipv4\" source address=\"$ip\" drop" ;;
        p|P) regla="rule family=\"ipv4\" source address=\"$ip\" accept" ;;
        *)
            mensaje_error "Acción no válida."
            pausa
            return 1
            ;;
    esac

    firewall-cmd $mod_perm --add-rich-rule="$regla" &>/dev/null
    if [ $? -eq 0 ]; then
        [ -n "$mod_perm" ] && firewall-cmd --reload &>/dev/null
        mensaje_ok "Regla para la dirección IP '$ip' establecida exitosamente."
    else
        mensaje_error "No se pudo aplicar la regla para la IP '$ip'."
    fi
    pausa
}

regla_combinada() {
    requiere_root || return 1
    local ip=""
    local puerto=""
    local proto=""
    local accion=""
    local tipo=""
    local mod_perm=""
    local regla=""

    echo "--- REGLA COMBINADA (IP A UN PUERTO ESPECÍFICO) ---"
    read -r -p "Ingrese IP de origen (ej: 192.168.1.50): " ip
    read -r -p "Ingrese puerto destino (ej: 3306): " puerto
    read -r -p "Protocolo (tcp/udp) [tcp]: " proto
    proto=${proto:-tcp}
    read -r -p "Acción (accept/drop/reject) [accept]: " accion
    accion=${accion:-accept}
    read -r -p "¿Aplicar de forma (P)ermanente o (T)emporal? (p/t): " tipo

    [ "$tipo" = "p" ] || [ "$tipo" = "P" ] && mod_perm="--permanent"

    regla="rule family=\"ipv4\" source address=\"$ip\" port port=\"$puerto\" protocol=\"$proto\" $accion"

    firewall-cmd $mod_perm --add-rich-rule="$regla" &>/dev/null
    if [ $? -eq 0 ]; then
        [ -n "$mod_perm" ] && firewall-cmd --reload &>/dev/null
        mensaje_ok "Regla combinada aplicada exitosamente."
    else
        mensaje_error "Error al aplicar la regla combinada en Firewalld."
    fi
    pausa
}

menu_firewall() {
    local opcion=""
    while [ "$opcion" != "0" ]; do
        titulo "S.I.G.S.M. - CORTAFUEGOS (FIREWALLD)"
        echo " 1) Ver zona activa y reglas vigentes"
        echo " 2) Abrir / Cerrar puertos"
        echo " 3) Bloquear / Permitir IP o Red"
        echo " 4) Regla combinada (IP a puerto específico)"
        echo " 0) Volver al menú principal"
        echo "==================================================================="
        read -r -p "Seleccione una opción: " opcion

        case "$opcion" in
            1) ver_reglas_firewall ;;
            2) gestionar_puertos ;;
            3) bloquear_ip ;;
            4) regla_combinada ;;
            0) break ;;
            *) mensaje_error "Opción no válida"; pausa ;;
        esac
    done
}
