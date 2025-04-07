FROM php:8.2-cli

RUN apt-get update -y && apt-get install -y \
    zip unzip git curl libzip-dev libpng-dev libfreetype6-dev \
    libicu-dev libxml2-dev libonig-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring zip xml intl

RUN mkdir /twilio
WORKDIR /twilio
ENV PATH="vendor/bin:$PATH"

COPY src src
COPY composer* ./

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN composer clear-cache && composer install --prefer-dist --no-interaction --verbose
