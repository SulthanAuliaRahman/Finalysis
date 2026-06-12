# ══════════════════════════════════════════════════════════════
# Dockerfile — Finalysis Laravel 12 (Production)
# Multi-stage: build assets → production image (Nginx + PHP-FPM)
# ══════════════════════════════════════════════════════════════

# ──────────────────────────────────────────────
# Stage 1: Node — Build Vite/Inertia assets
# ──────────────────────────────────────────────
FROM node:20-alpine AS node-builder

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --prefer-offline

COPY . .
RUN npm run build

# ──────────────────────────────────────────────
# Stage 2: Composer — Install PHP dependencies
# ──────────────────────────────────────────────
FROM composer:2.8 AS composer-builder

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --ignore-platform-reqs

COPY . .
RUN composer dump-autoload --optimize --no-dev

# ──────────────────────────────────────────────
# Stage 3: Production — Nginx + PHP-FPM
# ──────────────────────────────────────────────
FROM php:8.3-fpm-alpine AS production

LABEL maintainer="Finalysis"
LABEL description="Finalysis Laravel 12 Production Image"

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    libpq \
    libpng \
    libxml2 \
    oniguruma \
    curl \
    && apk add --no-cache --virtual .build-deps \
    postgresql-dev \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        opcache \
    && apk del .build-deps \
    && rm -rf /var/cache/apk/*

WORKDIR /var/www/html

# Copy PHP vendor dari stage composer
COPY --from=composer-builder /app/vendor ./vendor
COPY --from=composer-builder /app .

# Copy built assets dari stage node
COPY --from=node-builder /app/public/build ./public/build

# Copy konfigurasi Nginx dan Supervisor
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Permissions
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && mkdir -p /var/log/supervisor /var/log/nginx /run/nginx

EXPOSE 8080

# Supervisord mengelola Nginx + PHP-FPM sekaligus
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
