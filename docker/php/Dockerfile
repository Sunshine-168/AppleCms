FROM php:8.4-cli-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends libsqlite3-dev libzip-dev unzip git \
    && docker-php-ext-install pdo_sqlite bcmath pcntl \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
