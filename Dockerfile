# syntax=docker/dockerfile:1

FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN apk add --no-cache libzip-dev unzip \
    && docker-php-ext-install zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && composer install --no-dev --prefer-dist --no-progress --no-interaction

FROM php:8.3-cli AS app
WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y supervisor libzip-dev unzip \
    && docker-php-ext-install pdo_mysql opcache zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

COPY docker/supervisor /etc/supervisor

COPY --from=vendor /app/vendor vendor
COPY . .

RUN chmod +x docker/entrypoint.sh

ENTRYPOINT ["docker/entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisor/supervisord.conf"]
