FROM php:8.2-apache

# Extensiones PDO para conectar con MariaDB
RUN docker-php-ext-install pdo pdo_mysql mysqli

# mod_rewrite para el .htaccess (se usará en pasos siguientes para el ruteo REST)
RUN a2enmod rewrite
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

WORKDIR /var/www/html
COPY . .

# Crear la carpeta de documentos
RUN mkdir -p /var/www/html/uploads/pdf /var/www/html/uploads/documentos \
    && chown -R www-data:www-data /var/www/html/uploads \
    && chmod -R 755 /var/www/html/uploads

# Asignar propietario al proyecto y garantizar .htaccess para ruteo REST
RUN if [ ! -f /var/www/html/.htaccess ]; then \
        printf "RewriteEngine On\nRewriteCond %%{REQUEST_FILENAME} !-f\nRewriteCond %%{REQUEST_FILENAME} !-d\nRewriteRule ^api/documentos/publicos/?$ api/documentos/publicos.php [L,QSA]\nRewriteRule ^api/encuestas/resultados/?$ api/encuestas/index.php?resultados=1 [L,QSA]\nRewriteRule ^api/traslados/([0-9]+)/estado/?$ api/traslados/index.php?id=\$1&accion=estado [L,QSA]\nRewriteRule ^api/(vehiculos|choferes|enfermeros|pacientes|objetos|traslados|documentos|categorias|encuestas|usuarios)/([0-9]+)/?$ api/\$1/index.php?id=\$2 [L,QSA]\nRewriteRule ^api/(vehiculos|choferes|enfermeros|pacientes|objetos|traslados|documentos|categorias|encuestas|usuarios)/?$ api/\$1/index.php [L,QSA]\n" > /var/www/html/.htaccess; \
    fi \
    && chown -R www-data:www-data /var/www/html
