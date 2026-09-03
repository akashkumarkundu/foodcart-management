FROM php:8.4-cli-alpine

# Install system dependencies & PHP extensions
RUN apk add --no-cache \
    curl \
    git \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    sqlite \
    sqlite-dev \
    nodejs \
    npm

RUN docker-php-ext-install pdo pdo_sqlite pcntl bcmath opcache

# Get Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy application files
COPY . .

# Ensure storage directories & permissions
RUN mkdir -p storage/framework/cache/data \
             storage/framework/sessions \
             storage/framework/views \
             storage/logs \
             bootstrap/cache \
             database \
    && chmod -R 777 storage bootstrap/cache database

# Create default .env from .env.example
RUN cp .env.example .env

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Build frontend assets
RUN npm install && npm run build

# Setup entrypoint script and strip any CRLF line endings
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh && chmod +x /usr/local/bin/entrypoint.sh

ENV APP_NAME="Food Cart Management"
ENV APP_ENV="production"
ENV APP_KEY="base64:9vuIuY6u+sKUzRjEzVuTkyCw/+nTbtCVqHsnycZpObA="
ENV APP_DEBUG="false"
ENV DB_CONNECTION="sqlite"
ENV DB_DATABASE="/app/database/database.sqlite"
ENV PORT="8080"

EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
