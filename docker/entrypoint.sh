#!/bin/bash
set -e

# Generate APP_KEY if missing (Azure App Settings should normally provide this)
if [ -z "$APP_KEY" ]; then
    echo "WARNING: APP_KEY is not set. Generating a temporary one (set APP_KEY in Azure App Settings for production)."
    php artisan key:generate --force
fi

# Cache config, routes, views for performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations (safe to run on every deploy)
php artisan migrate --force

# Create storage symlink (for local fallback; not used when Azure Blob is the disk)
php artisan storage:link || true

# Hand off to CMD (supervisord)
exec "$@"