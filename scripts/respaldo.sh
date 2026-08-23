#!/bin/bash

respaldoLocal() {
    echo ""
    echo "--- RESPALDO LOCAL ---"
    fecha=$(date +"%Y%m%d_%H%M%S")
    mkdir -p /var/backups/sigsm
    
    echo "1) Exportando Base de Datos MariaDB..."
    mysqldump -u root --all-databases > "/var/backups/sigsm/db_$fecha.sql" 2>/dev/null
    
    echo "2) Empaquetando archivos web y base de datos..."
    tar -czf "/var/backups/sigsm/respaldo_$fecha.tar.gz" -C /var/www/html . -C /var/backups/sigsm "db_$fecha.sql" 2>/dev/null
    rm -f "/var/backups/sigsm/db_$fecha.sql"
    
    echo "Respaldo generado con éxito en: /var/backups/sigsm/respaldo_$fecha.tar.gz"
    read -p "Presione ENTER para continuar."
}

respaldoRemoto() {
    echo ""
    echo "--- TRANSFERENCIA REMOTA (SCP) ---"
    read -p "Ingrese la ruta del archivo a enviar (ej: /var/backups/sigsm/archivo.tar.gz): " rutaArchivo
    read -p "Ingrese la IP del servidor remoto: " ipRemota
    read -p "Ingrese el usuario remoto: " userRemoto
    read -p "Ingrese la ruta destino remota (ej: /home/sigsm/): " rutaRemota
    
    if [ -f "$rutaArchivo" ]; then
        scp "$rutaArchivo" "$userRemoto@$ipRemota:$rutaRemota"
        echo "Transferencia SCP completada."
    else
        echo "El archivo no existe."
    fi
    read -p "Presione ENTER para continuar."
}

programarCron() {
    echo ""
    echo "--- PROGRAMAR RESPALDO CON CRON ---"
    echo "Ejemplo para todos los días a las 02:00 AM: 0 2 * * *"
    read -p "Ingrese la configuración de tiempo para CRON: " tiempoCron
    
    # Crear script auxiliar simple para el cron
    cat << 'CRON_FILE' > /opt/sigsm/scripts/tarea_auto.sh
#!/bin/bash
fecha=$(date +"%Y%m%d_%H%M%S")
mkdir -p /var/backups/sigsm
mysqldump -u root --all-databases > /var/backups/sigsm/db_auto.sql 2>/dev/null
tar -czf /var/backups/sigsm/auto_$fecha.tar.gz -C /var/www/html . /var/backups/sigsm/db_auto.sql 2>/dev/null
rm -f /var/backups/sigsm/db_auto.sql
CRON_FILE
    chmod +x /opt/sigsm/scripts/tarea_auto.sh
    
 
   (crontab -l 2>/dev/null; echo "$tiempoCron /opt/sigsm/scripts/tarea_auto.sh") | crontab -
    echo "Respaldo programado exitosamente en el CRON."
    read -p "Presione ENTER para continuar."
}

verCron() {
    echo ""
    echo "--- TAREAS PROGRAMADAS EN CRONTAB ---"
    crontab -l
    read -p "Presione ENTER para continuar."
}

menuRespaldos() {
    opcBkp=99
    while [ "$opcBkp" -ne 0 ]; do
        clear
        echo "============================================="
        echo "       S.I.G.S.M. - GESTIÓN DE RESPALDOS     "
        echo "============================================="
        echo "1) Crear respaldo local (BD + Web con tar)"
        echo "2) Transferir respaldo a otro servidor (SCP)"
        echo "3) Programar respaldo automático (CRON)"
        echo "4) Ver tareas agendadas en CRON"
        echo "0) Volver al menú principal"
        echo "============================================="
        read -p "Seleccione una opción: " opcBkp
        case $opcBkp in
            1) respaldoLocal ;;
            2) respaldoRemoto ;;
            3) programarCron ;;
            4) verCron ;;
            0) echo "Volviendo..." ;;
            *) echo "Opción no válida." ;;
        esac
    done
}
