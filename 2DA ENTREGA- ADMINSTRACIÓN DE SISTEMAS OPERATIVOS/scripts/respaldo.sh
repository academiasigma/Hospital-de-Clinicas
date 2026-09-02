#!/bin/bash
#Módulo de respaldos!!


respaldo_local() {
    requiere_root || return 1
    local fecha
    local destino="/var/backups/sigsm"
    fecha=$(date +"%Y%m%d_%H%M%S")

    echo "--- RESPALDO LOCAL (BD + WEB CON TAR) ---"
    mkdir -p "$destino"

    echo "[*] Extrayendo volcado de base de datos..."
    mysqldump -u root --all-databases > "$destino/db_${fecha}.sql" 2>/dev/null

    echo "[*] Empaquetando y comprimiendo con tar..."
    tar -czf "$destino/backup_${fecha}.tar.gz" -C /var/www/html . -C "$destino" "db_${fecha}.sql" 2>/dev/null

    rm -f "$destino/db_${fecha}.sql"

    if [ -f "$destino/backup_${fecha}.tar.gz" ]; then
        mensaje_ok "Respaldo generado con éxito en: $destino/backup_${fecha}.tar.gz"
    else
        mensaje_error "Error al crear el archivo comprimido de respaldo."
    fi
    pausa
}

respaldo_remoto() {
    local archivo=""
    local ip_remota=""
    local user_remoto=""
    local ruta_remota=""

    echo "--- TRANSFERENCIA REMOTA (SCP) ---"
    read -r -p "Ruta del archivo a enviar (ej: /var/backups/sigsm/backup.tar.gz): " archivo
    if [ ! -f "$archivo" ]; then
        mensaje_error "El archivo indicado no existe en el sistema local."
        pausa
        return 1
    fi

    read -r -p "IP del servidor remoto: " ip_remota
    read -r -p "Usuario remoto: " user_remoto
    read -r -p "Ruta destino remota (ej: /home/sigsm/): " ruta_remota

    if [ -z "$ip_remota" ] || [ -z "$user_remoto" ] || [ -z "$ruta_remota" ]; then
        mensaje_error "Todos los parámetros son obligatorios."
        pausa
        return 1
    fi

    echo "[*] Transfiriendo archivo..."
    scp "$archivo" "${user_remoto}@${ip_remota}:${ruta_remota}"

    if [ $? -eq 0 ]; then
        mensaje_ok "Transferencia finalizada con éxito."
    else
        mensaje_error "Falló la transferencia mediante SCP."
    fi
    pausa
}

programar_cron() {
    requiere_root || return 1
    local tiempo_cron=""
    local script_auto="/opt/sigsm/scripts/tarea_auto.sh"

    echo "--- PROGRAMAR RESPALDO CON CRON ---"
    echo "Formato: Minuto Hora DíaMes Mes DíaSemana (Ej: 0 2 * * * para las 02:00 AM)"
    read -r -p "Ingrese la configuración de tiempo: " tiempo_cron

    if [ -z "$tiempo_cron" ]; then
        mensaje_error "Debe ingresar una expresión de tiempo válida."
        pausa
        return 1
    fi

    if [ ! -f "$script_auto" ]; then
        mensaje_error "No se encuentra el ejecutable programado en $script_auto."
        pausa
        return 1
    fi

    chmod +x "$script_auto"

    (crontab -l 2>/dev/null; echo "$tiempo_cron $script_auto") | crontab -

    if [ $? -eq 0 ]; then
        mensaje_ok "Tarea programada correctamente en el demonio CRON."
    else
        mensaje_error "No se pudo agendar la tarea en crontab."
    fi
    pausa
}

ver_cron() {
    echo "--- TAREAS AGENDADAS EN CRONTAB ---"
    crontab -l 2>/dev/null
    if [ $? -ne 0 ]; then
        echo "No existen tareas agendadas para el usuario actual."
    fi
    echo ""
    pausa
}

menu_respaldos() {
    local opcion=""
    while [ "$opcion" != "0" ]; do
        titulo "S.I.G.S.M. - GESTIÓN DE RESPALDOS"
        echo " 1) Crear respaldo local (BD + Web con tar)"
        echo " 2) Transferir respaldo a otro servidor (SCP)"
        echo " 3) Programar respaldo automático (CRON)"
        echo " 4) Ver tareas agendadas en CRON"
        echo " 0) Volver al menú principal"
        echo "==================================================================="
        read -r -p "Seleccione una opción: " opcion

        case "$opcion" in
            1) respaldo_local ;;
            2) respaldo_remoto ;;
            3) programar_cron ;;
            4) ver_cron ;;
            0) break ;;
            *) mensaje_error "Opción no válida"; pausa ;;
        esac
    done
}
