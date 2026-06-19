# ============================================================
# Stage 1: Node — build frontend assets
# ============================================================
FROM node:20-alpine AS node-builder

WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY resources/ resources/
COPY vite.config.js ./
COPY public/ public/

RUN npm run build

# ============================================================
# Stage 2: PHP — install dependencies and serve the app
# ============================================================
FROM php:8.3-cli-alpine AS php-runner

# Install system dependencies + PHP extensions
RUN apk add --no-cache \
    postgresql-libs \
    postgresql-dev \
    oniguruma-dev \
    libzip-dev \
    libzip \
    zip \
    unzip \
    curl \
 && docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pgsql \
    mbstring \
    zip \
    opcache \
 && apk del postgresql-dev oniguruma-dev libzip-dev

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy composer files and install PHP dependencies (no dev)
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Copy the rest of the application
COPY . .

# Copy compiled frontend assets from the Node stage
COPY --from=node-builder /app/public/build public/build

# Set up storage and bootstrap cache directories
RUN mkdir -p storage/framework/{sessions,views,cache} \
             storage/logs \
             bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

# Run post-install scripts now that the full app is present
RUN composer run-script post-autoload-dump

# Cache Laravel config, routes, and views for production
# NOTE: caching is done at runtime (CMD) so env vars are available

EXPOSE 8000

# Use shell form so $PORT is expanded at runtime
CMD php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
