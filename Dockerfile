FROM php:8.2-apache

# 1. Instalar las extensiones de PostgreSQL (necesarias para conectar con Neon)
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql

# 2. Copiar todo el código de tu proyecto al directorio web de Apache
COPY . /var/www/html/

# 3. Habilitar mod_rewrite (por si tu proyecto usa URLs amigables con .htaccess)
RUN a2enmod rewrite

# 4. Ajustar permisos de la carpeta web
RUN chown -R www-data:www-data /var/www/html