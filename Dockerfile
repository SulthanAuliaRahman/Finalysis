FROM php:8.3-cli

# System dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    zip \
    unzip \
    libpq-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Node.js 20
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Copy composer files dulu (layer cache)
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader

# Copy package.json dulu (layer cache)
COPY package.json package-lock.json ./
RUN npm ci

# Copy semua source code
COPY . .

RUN npm run build

# Finalize composer
RUN composer dump-autoload --optimize

# Permission untuk storage dan cache
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8080

# Script untuk jalankan Laravel + Vite bersamaan
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]