# syntax=docker/dockerfile:1

FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN apk add --no-cache libzip-dev unzip \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && docker-php-ext-install zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps \
    && composer install --no-dev --prefer-dist --no-progress --no-interaction

FROM php:8.3-cli AS app
WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends supervisor libzip-dev unzip \
    && apt-get install -y --no-install-recommends $PHPIZE_DEPS \
    && docker-php-ext-install pdo_mysql opcache zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false $PHPIZE_DEPS \
    && rm -rf /var/lib/apt/lists/*

COPY docker/supervisor /etc/supervisor

COPY --from=vendor /app/vendor vendor
COPY . .

RUN chmod +x docker/entrypoint.sh

ENTRYPOINT ["docker/entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisor/supervisord.conf"]
