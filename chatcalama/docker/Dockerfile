FROM php:8.2-apache

RUN docker-php-ext-install mysqli

RUN a2enmod rewrite

WORKDIR /var/www/html

# Copia proyecto (en docker-compose usamos volumen, esto es opcional)
