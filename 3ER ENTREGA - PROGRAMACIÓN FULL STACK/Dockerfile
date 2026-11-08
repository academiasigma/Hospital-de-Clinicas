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

# Asignar propietario al proyecto
RUN chown -R www-data:www-data /var/www/html
