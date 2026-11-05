FROM php:8.2-apache

# 1. Instalar las extensiones de PostgreSQL (necesarias para Neon)
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql

# 2. Copiar todo el código de tu proyecto al directorio web de Apache
COPY . /var/www/html/

# 3. CONFIGURAR QUE LOGIN.PHP SEA LA PÁGINA DE INICIO POR DEFECTO
RUN echo "DirectoryIndex login.php" >> /etc/apache2/apache2.conf

# 4. Habilitar mod_rewrite (URLs amigables)
RUN a2enmod rewrite

# 5. Ajustar permisos de la carpeta web
RUN chown -R www-data:www-data /var/www/html