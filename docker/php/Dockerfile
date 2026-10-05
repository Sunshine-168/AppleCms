FROM php:8.4-cli-bookworm

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN apt-get update \
    && apt-get install -y --no-install-recommends libsqlite3-dev libzip-dev unzip git \
    && docker-php-ext-install pdo_sqlite pdo_mysql bcmath pcntl zip \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
