FROM nginx:1.28-alpine

COPY ./docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY ./app/public/ /var/www/html/

EXPOSE 80