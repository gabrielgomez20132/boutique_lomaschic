FROM php:7.4-fpm

# Instalar dependencias
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libonig-dev libxml2-dev zip curl \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

# Instalar Composer v1 (Laravel 5.5 no anda bien con Composer 2)
RUN curl -sS https://getcomposer.org/installer | php -- --version=1.10.26 --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www

# Copiar el script de entrypoint
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Configurar el entrypoint
ENTRYPOINT ["docker-entrypoint.sh"]

# Comando por defecto
CMD ["php-fpm"]