FROM php:8.2-cli

# Instalacja wymaganych zależności systemowych
RUN apt-get update -y && apt-get install -y \
    zip unzip git curl libzip-dev libpng-dev libfreetype6-dev \
    libicu-dev libxml2-dev libonig-dev libjpeg-dev libgd-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring zip xml intl gd

# Instalacja Composer bezpośrednio w kontenerze
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Tworzenie katalogu roboczego
RUN mkdir /twilio
WORKDIR /twilio

# Kopiowanie plików projektu do kontenera
COPY composer.json composer.lock ./
COPY src/ /twilio/src/

# Uruchomienie Composer w celu instalacji zależności
RUN composer install --prefer-dist --no-interaction --no-scripts

# Komenda domyślna dla kontenera (możesz dostosować do swoich potrzeb)
CMD ["php", "bin/console", "server:run"]
