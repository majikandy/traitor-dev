FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    sqlite3 \
    libsqlite3-dev \
    unzip \
    git \
    && docker-php-ext-install pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/portal

COPY portal/composer.json portal/composer.lock ./
RUN composer install --no-scripts --no-autoloader

COPY portal/ .

RUN composer dump-autoload --optimize

RUN cp -n .env.example .env || true \
    && touch database/database.sqlite \
    && php artisan key:generate \
    && php artisan migrate --force

EXPOSE 9000

CMD ["php-fpm"]
