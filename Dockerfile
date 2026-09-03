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

# Set permissions
RUN chmod -R 777 storage bootstrap/cache

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Build frontend assets
RUN npm install && npm run build

# Setup entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENV PORT=8080
EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
