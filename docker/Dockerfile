FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    default-mysql-client \
    unzip \
    git \
    libzip-dev \
    && docker-php-ext-install pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/portal

COPY portal/composer.json portal/composer.lock ./
RUN composer install --no-scripts --no-autoloader

COPY portal/ .
RUN mkdir -p bootstrap/cache && chmod 775 bootstrap/cache
RUN composer dump-autoload --optimize

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

RUN mkdir -p /var/www/sites && chown www-data:www-data /var/www/sites

EXPOSE 9000

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
