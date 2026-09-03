#!/bin/sh

# If .env does not exist, copy from .env.example
if [ ! -f /app/.env ]; then
    if [ -f /app/.env.example ]; then
        cp /app/.env.example /app/.env
    else
        touch /app/.env
    fi
fi

# Ensure SQLite database directory and file exist
mkdir -p /app/database
if [ ! -f /app/database/database.sqlite ]; then
    touch /app/database/database.sqlite
fi

# Ensure storage directories exist and are writable
mkdir -p /app/storage/framework/cache/data \
         /app/storage/framework/sessions \
         /app/storage/framework/views \
         /app/storage/logs \
         /app/bootstrap/cache
chmod -R 777 /app/storage /app/bootstrap/cache /app/database

# Run database migrations and seeders
php artisan migrate --force || true
php artisan db:seed --force || true

# Determine port (Railway and Render provide $PORT)
TARGET_PORT="${PORT:-8080}"
echo "🚀 Food Cart Management running on port $TARGET_PORT"

exec php artisan serve --host=0.0.0.0 --port="$TARGET_PORT"
