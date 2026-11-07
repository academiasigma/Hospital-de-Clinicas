#!/bin/bash
#Respaldo automatizado para CRON

PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin
FECHA=$(/bin/date +"%Y%m%d_%H%M%S")
DESTINO="/var/backups/sigsm"
CONTENEDOR="sigsm_mariadb"

# Parámetros del servidor remoto de respaldo (Máquina Windows)
REMOTO_USER="facundo"
REMOTO_IP="192.168.1.246"
REMOTO_DIR="Desktop/respaldos_sigsm/"
SSH_KEY="/home/sigsm/.ssh/id_ed25519"

/bin/mkdir -p "$DESTINO"

# 1. Volcado silencioso de la base de datos desde el contenedor Docker
/usr/bin/docker exec "$CONTENEDOR" mysqldump -u root -proot --all-databases > "$DESTINO/db_auto.sql" 2>/dev/null

# 2. Empaquetado comprimido del sitio web y del volcado SQL
if [ -s "$DESTINO/db_auto.sql" ]; then
    ARCHIVO_BKP="$DESTINO/auto_${FECHA}.tar.gz"
    /usr/bin/tar -czf "$ARCHIVO_BKP" -C /var/www/html . -C "$DESTINO" db_auto.sql 2>/dev/null
    
    # 3. Transferencia desatendida mediante SCP y clave SSH hacia Windows
    if [ -f "$ARCHIVO_BKP" ] && [ -f "$SSH_KEY" ]; then
        /usr/bin/scp -i "$SSH_KEY" -o StrictHostKeyChecking=no "$ARCHIVO_BKP" "${REMOTO_USER}@${REMOTO_IP}:${REMOTO_DIR}" 2>/dev/null
    fi
fi

# 4. Limpieza del archivo intermedio
/bin/rm -f "$DESTINO/db_auto.sql"

exit 0
