FROM php:8.1-apache-bookworm

ENV TZ=Asia/Shanghai \
    APACHE_DOCUMENT_ROOT=/var/www/html

RUN apt-get update && apt-get install -y --no-install-recommends \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libzip-dev \
        libonig-dev \
        libxml2-dev \
        unzip \
        curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo_mysql mysqli gd zip mbstring opcache \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY docker/php.ini /usr/local/etc/php/conf.d/maccms.ini
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/entrypoint.sh /usr/local/bin/maccms-entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/maccms-entrypoint.sh \
    && chmod +x /usr/local/bin/maccms-entrypoint.sh

WORKDIR /var/www/html
EXPOSE 80

ENTRYPOINT ["maccms-entrypoint.sh"]
