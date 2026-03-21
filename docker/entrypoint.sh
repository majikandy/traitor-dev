#!/bin/bash
set -e

cd /var/www/portal

# Ensure directories exist (volume mount overwrites build artifacts)
mkdir -p bootstrap/cache storage/framework/{cache,sessions,views} storage/logs
chown -R www-data:www-data bootstrap/cache storage
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

# Wait for MySQL to be ready
echo "Waiting for MySQL..."
if [ -z "$DB_HOST" ]; then
    echo "ERROR: DB_HOST is not set" >&2
    exit 1
fi
while ! php -r "new PDO('mysql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));" 2>/dev/null; do
    sleep 1
done
echo "MySQL is ready."

# Run migrations
php artisan migrate --force

# Ensure sites directory exists
mkdir -p /var/www/sites

exec "$@"
