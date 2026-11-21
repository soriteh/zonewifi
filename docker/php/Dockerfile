FROM php:8.3-fpm-alpine

RUN apk add --no-cache $PHPIZE_DEPS bash git unzip icu-dev libzip-dev postgresql-dev oniguruma-dev \
    && docker-php-ext-install pdo_pgsql mbstring bcmath intl opcache zip pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY backend/composer.json ./composer.json
RUN composer install --no-interaction --prefer-dist --no-progress --no-scripts

COPY backend/ ./

RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && composer dump-autoload --optimize --no-interaction \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
