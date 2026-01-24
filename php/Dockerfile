FROM php:8.3-fpm

ARG UID=1000
ARG GID=1000

RUN groupmod -o -g $GID www-data && usermod -o -u $UID -g $GID www-data

RUN apt-get update && apt-get install -y \
    libpq-dev zip unzip git curl libzip-dev \
    librdkafka-dev libsodium-dev netcat-openbsd \
    && docker-php-ext-install pdo pdo_pgsql zip sodium opcache \
    && pecl install rdkafka redis \
    && docker-php-ext-enable rdkafka redis opcache \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copia primero composer.* para aprovechar cache
COPY composer.json composer.lock ./
# Instalar dependencias sin generar autoloader (porque faltan archivos app/) ni scripts
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

# Luego copia el resto
COPY . .

# Generar autoloader ahora que los archivos existen
RUN composer dump-autoload --optimize

RUN chmod +x scripts/init.sh \
    && mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
