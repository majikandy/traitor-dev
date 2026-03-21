#!/bin/bash
set -e

cd /var/www/portal

# Ensure directories exist (volume mount overwrites build artifacts)
mkdir -p bootstrap/cache storage/framework/{cache,sessions,views} storage/logs
chmod -R 775 bootstrap/cache storage

# Install dependencies (volume mount overwrites vendor from build)
composer install --no-interaction --quiet

# Ensure .env exists with app key
if [ ! -f .env ]; then
    cp .env.example .env
fi
if ! grep -q "^APP_KEY=base64:" .env; then
    php artisan key:generate
fi

# Ensure sqlite DB exists and is migrated
touch database/database.sqlite
php artisan migrate --force

# Ensure sites directory exists
mkdir -p /var/www/sites

exec "$@"
