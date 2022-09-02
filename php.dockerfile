FROM composer:latest as base
COPY . /var/www/html
RUN chown -R www-data:www-data /var/www/html
USER www-data
WORKDIR /var/www/html
RUN composer install --no-dev

FROM php:7.3-fpm
RUN apt-get update && apt-get install -y zip unzip libpng-dev libzip-dev && apt-get clean
RUN docker-php-ext-configure gd && docker-php-ext-install gd mysqli zip
COPY --from=base /var/www/html /var/www/html
RUN chown -R www-data:www-data /var/www/html
WORKDIR /var/www/html
