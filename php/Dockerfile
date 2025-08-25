FROM php:8.3-fpm

# Instalar dependencias del sistema y extensiones requeridas
RUN apt-get update && apt-get install -y \
    libpq-dev zip unzip curl git libzip-dev \
    librdkafka-dev libsodium-dev netcat-openbsd \
    && docker-php-ext-install pdo pdo_pgsql zip sodium \
    && pecl install rdkafka \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-enable rdkafka \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

# Hacer ejecutables los scripts
RUN chmod +x scripts/init.sh

# Instalar dependencias de Composer
RUN composer install --no-dev --optimize-autoloader

# Configurar permisos de storage
RUN mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Exponer el puerto FPM
EXPOSE 9000

CMD ["php-fpm"]
