#!/bin/bash
set -e

cd /var/www/portal

# Ensure .env exists
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
fi

# Ensure sqlite DB exists and is migrated
touch database/database.sqlite
php artisan migrate --force

# Ensure sites directory exists
mkdir -p /var/www/sites

exec "$@"
