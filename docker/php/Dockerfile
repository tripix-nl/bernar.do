FROM php:7.3-fpm-alpine

WORKDIR /var/www

RUN apk update && apk add libpng libpng-dev libjpeg-turbo-dev libwebp-dev zlib-dev libxpm-dev && docker-php-ext-install pdo pdo_mysql bcmath gd exif pcntl

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY laravel.ini /usr/local/etc/php/conf.d/99-laravel.ini
