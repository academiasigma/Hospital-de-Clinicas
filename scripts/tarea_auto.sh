#!/bin/bash
fecha=$(date +"%Y%m%d_%H%M%S")
mkdir -p /var/backups/sigsm
mysqldump -u root --all-databases > /var/backups/sigsm/db_auto.sql 2>/dev/null
tar -czf /var/backups/sigsm/auto_$fecha.tar.gz -C /var/www/html . /var/backups/sigsm/db_auto.sql 2>/dev/null
rm -f /var/backups/sigsm/db_auto.sql
