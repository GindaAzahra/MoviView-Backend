#!/bin/bash

# Change Apache port to Render's/Railway's dynamic PORT
if [ ! -z "$PORT" ]; then
    sed -i "s/80/$PORT/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf
fi

# Run migrations
php artisan migrate --force

# Run cache optimizations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start Apache in the foreground
exec apache2-foreground
