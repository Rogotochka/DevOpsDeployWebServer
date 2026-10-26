FROM php:8.4-alpine

RUN apt-get update && apt-get install -y libpq-dev \
	&& docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
	&& docker-php-ext-install pdo pdo_pgsql pgsql

COPY --chown=www-data:www-data app/src/ /var/www/src/
COPY --chown=www-data:www-data app/public/ /var/www/html/
WORKDIR /var/www