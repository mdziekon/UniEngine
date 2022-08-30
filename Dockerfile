FROM php:7.3-apache
RUN apt-get update && apt-get install -y zip unzip libpng-dev libzip-dev && apt-get clean
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
RUN docker-php-ext-configure gd && docker-php-ext-install gd mysqli zip
COPY . /var/www/html
WORKDIR /var/www/html
RUN chown -R www-data:www-data /var/www/html
USER www-data
RUN composer install --no-dev
USER root