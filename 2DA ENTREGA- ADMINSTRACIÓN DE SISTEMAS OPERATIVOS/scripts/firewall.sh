#!/bin/bash

verReglas() {
    echo ""
    echo "--- ESTADO DE FIREWALLD ---"
    firewall-cmd --get-active-zones
    echo ""
    firewall-cmd --list-all
    read -p "Presione ENTER para continuar."
}

gestionarPuertos() {
    echo ""
    echo "--- GESTIÓN DE PUERTOS ---"
    read -p "Ingrese puerto y protocolo (ej: 8080/tcp o 3306/tcp): " puerto
    read -p "¿Desea (A)brir o (C)errar el puerto? (a/c): " accion
    read -p "¿Aplicar de forma (P)ermanente o (T)emporal? (p/t): " tipo
    
    flag=""
    if [ "$tipo" = "p" ] || [ "$tipo" = "P" ]; then
        flag="--permanent"
    fi
    
    if [ "$accion" = "a" ] || [ "$accion" = "A" ]; then
        firewall-cmd $flag --add-port="$puerto"
        echo "Puerto habilitado."
    else
        firewall-cmd $flag --remove-port="$puerto"
        echo "Puerto deshabilitado."
    fi
    
    if [ -n "$flag" ]; then
        firewall-cmd --reload
    fi
    read -p "Presione ENTER para continuar."
}

bloquearIp() {
    echo ""
    echo "--- BLOQUEAR / PERMITIR IP O RED ---"
    read -p "Ingrese la IP o Red (ej: 192.168.1.50): " ipDestino
    read -p "¿Desea (B)loquear o (P)ermitir? (b/p): " accionIp
    read -p "¿Aplicar de forma (P)ermanente o (T)emporal? (p/t): " tipo
    
    flag=""
    if [ "$tipo" = "p" ] || [ "$tipo" = "P" ]; then
        flag="--permanent"
    fi
    
    if [ "$accionIp" = "b" ] || [ "$accionIp" = "B" ]; then
        firewall-cmd $flag --add-rich-rule="rule family=\"ipv4\" source address=\"$ipDestino\" drop"
        echo "IP bloqueada."
    else
        firewall-cmd $flag --add-rich-rule="rule family=\"ipv4\" source address=\"$ipDestino\" accept"
        echo "IP permitida."
    fi
    
    if [ -n "$flag" ]; then
        firewall-cmd --reload
    fi
    read -p "Presione ENTER para continuar."
}

reglaCombinada() {
    echo ""
    echo "--- REGLA COMBINADA (IP A UN PUERTO ESPECÍFICO) ---"
    read -p "Ingrese la IP de origen (ej: 192.168.1.50): " ipOrigen
    read -p "Ingrese el puerto (ej: 3306): " puertoDestino
    read -p "Protocolo (tcp/udp) [tcp]: " proto
    proto=${proto:-tcp}
    read -p "Acción (accept/drop) [accept]: " accionRegla
    accionRegla=${accionRegla:-accept}
    read -p "¿Aplicar de forma (P)ermanente o (T)emporal? (p/t): " tipo
    
    flag=""
    if [ "$tipo" = "p" ] || [ "$tipo" = "P" ]; then
        flag="--permanent"
    fi
    
    firewall-cmd $flag --add-rich-rule="rule family=\"ipv4\" source address=\"$ipOrigen\" port port=\"$puertoDestino\" protocol=\"$proto\" $accionRegla"
    
    if [ -n "$flag" ]; then
        firewall-cmd --reload
    fi
    echo "Regla combinada aplicada correctamente."
    read -p "Presione ENTER para continuar."
}

menuFirewall() {
    opcFw=99
    while [ "$opcFw" -ne 0 ]; do
        clear
        echo "============================================="
        echo "       S.I.G.S.M. - CORTAFUEGOS FIREWALLD    "
        echo "============================================="
        echo "1) Ver zona activa y reglas vigentes"
        echo "2) Bloquear / Desbloquear puertos"
        echo "3) Bloquear / Desbloquear IP o Red"
        echo "4) Regla combinada (IP a puerto específico)"
        echo "0) Volver al menú principal"
        echo "============================================="
        read -p "Seleccione una opción: " opcFw
        case $opcFw in
            1) verReglas ;;
            2) gestionarPuertos ;;
            3) bloquearIp ;;
            4) reglaCombinada ;;
            0) echo "Volviendo..." ;;
            *) echo "Opción no válida." ;;
        esac
    done
}
