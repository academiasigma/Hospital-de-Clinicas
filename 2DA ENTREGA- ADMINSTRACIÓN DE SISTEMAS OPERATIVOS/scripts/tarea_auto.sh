#!/bin/bash
#Script de respaldo automatizado con CRON!!

PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin
FECHA=$(/bin/date +"%Y%m%d_%H%M%S")
DESTINO="/var/backups/sigsm"

/bin/mkdir -p "$DESTINO"

/usr/bin/mysqldump -u root --all-databases > "$DESTINO/db_auto.sql" 2>/dev/null
/usr/bin/tar -czf "$DESTINO/auto_${FECHA}.tar.gz" -C /var/www/html . -C "$DESTINO" db_auto.sql 2>/dev/null
/bin/rm -f "$DESTINO/db_auto.sql"

exit 0
