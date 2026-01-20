#!/bin/bash
set -e

# Change Apache port to Railway/Render dynamic PORT
if [ ! -z "$PORT" ]; then
    echo "Configuring Apache to listen on port $PORT"
    sed -i "s/80/$PORT/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf
fi

# 1. Clear configuration cache first
# This is vital to ensure Laravel ignores any cached local settings and reads Railway's environment variables.
echo "Clearing Laravel configuration cache..."
php artisan config:clear

# 2. Run migrations
# We do this before caching again to ensure the database schema is up to date.
echo "Running database migrations..."
php artisan migrate --force

# 3. Create fresh cache for production performance
echo "Caching configuration and routes..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Clear application cache (optional, but good for fresh starts)
echo "Clearing application cache..."
php artisan cache:clear

# Start Apache in the foreground
echo "Starting Apache..."
exec apache2-foreground
