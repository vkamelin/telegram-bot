# syntax=docker/dockerfile:1

FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-progress --no-interaction

FROM php:8.3-cli AS app
WORKDIR /var/www/html

RUN docker-php-ext-install pdo_mysql opcache

COPY --from=vendor /app/vendor vendor
COPY . .

RUN chmod +x docker/entrypoint.sh

EXPOSE 8080

ENTRYPOINT ["docker/entrypoint.sh"]
CMD ["php", "-S", "0.0.0.0:8080", "-t", "public"]
