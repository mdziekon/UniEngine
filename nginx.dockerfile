FROM composer:latest as base
COPY . /var/www/html
RUN chown -R www-data:www-data /var/www/html
USER www-data
WORKDIR /var/www/html
RUN composer install --no-dev

FROM nginx:stable
COPY --from=base /var/www/html /var/www/html
COPY nginx.conf /etc/nginx/conf.d/default.conf
WORKDIR /var/www/html
