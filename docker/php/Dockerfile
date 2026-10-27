FROM php:8.3-fpm-alpine

RUN apk add --no-cache postgresql-dev libonig-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring

WORKDIR /var/www
COPY --chown=www-data:www-data app/src/ /var/www/src/
COPY --chown=www-data:www-data app/public/ /var/www/html/