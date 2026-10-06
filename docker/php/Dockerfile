# PHP 8.1 + Apache, matching production (cPanel ea-php81)
FROM php:8.1-apache

RUN apt-get update \
 && apt-get install -y --no-install-recommends \
      libpng-dev libjpeg62-turbo-dev libfreetype6-dev libzip-dev libicu-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j"$(nproc)" gd mysqli pdo_mysql zip intl bcmath exif \
 && a2enmod rewrite headers \
 && rm -rf /var/lib/apt/lists/*

COPY apache-rankers.conf /etc/apache2/conf-enabled/rankers.conf
COPY php.ini /usr/local/etc/php/conf.d/zz-rankers.ini
