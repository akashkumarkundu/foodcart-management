#!/bin/sh
set -e

# Ensure SQLite database exists
if [ ! -f /app/database/database.sqlite ]; then
    touch /app/database/database.sqlite
fi

# Ensure storage directories exist and are writable
mkdir -p /app/storage/framework/cache/data \
         /app/storage/framework/sessions \
         /app/storage/framework/views \
         /app/storage/logs
chmod -R 777 /app/storage /app/bootstrap/cache

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Run migrations and initial seeds
php artisan migrate --force
php artisan db:seed --force

# Clear and optimize Laravel caches
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Determine port (Render and Railway set $PORT)
TARGET_PORT=${PORT:-8080}
echo "Starting Food Cart Management Server on port $TARGET_PORT..."

exec php artisan serve --host=0.0.0.0 --port="$TARGET_PORT"
