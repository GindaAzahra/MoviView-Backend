#!/bin/bash
set -e

# Change Apache port to Render's/Railway's dynamic PORT
if [ ! -z "$PORT" ]; then
    sed -i "s/80/$PORT/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf
fi

# Clear cache first to ensure we have the latest environment variables
php artisan config:clear
php artisan cache:clear

# Run migrations
php artisan migrate --force

# Run cache optimizations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start Apache in the foreground
exec apache2-foreground
