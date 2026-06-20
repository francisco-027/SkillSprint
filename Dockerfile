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
    libpng \
    libpng-dev \
    libjpeg-turbo \
    libjpeg-turbo-dev \
    freetype \
    freetype-dev \
    zip \
    unzip \
    curl \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pgsql \
    mbstring \
    zip \
    gd \
    opcache \
 && apk del postgresql-dev oniguruma-dev libzip-dev libpng-dev libjpeg-turbo-dev freetype-dev

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

# Use shell form so $PORT is expanded at runtime.
# A background queue worker processes AI generation jobs so they don't block
# (and time out) the HTTP request; the web server runs in the foreground.
CMD php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan migrate --force \
 && (php artisan queue:work --tries=2 --timeout=180 --sleep=2 --rest=1 > /dev/null 2>&1 &) \
 && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
