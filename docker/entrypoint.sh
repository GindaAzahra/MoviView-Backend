#!/bin/bash

set -e

echo "Starting Laravel application..."

# Debug: Print environment variables
echo "DB_HOST: ${DB_HOST:-not set}"
echo "DB_DATABASE: ${DB_DATABASE:-not set}"

# Wait for MySQL to be ready
if [ -n "$DB_HOST" ] && [ "$DB_HOST" != "" ]; then
    echo "Waiting for MySQL at $DB_HOST:$DB_PORT..."
    max_attempts=30
    attempt=0
    
    until php -r "try { new PDO('mysql:host=${DB_HOST};port=${DB_PORT}', '${DB_USERNAME}', '${DB_PASSWORD}'); echo 'Connected'; } catch(PDOException \$e) { exit(1); }" 2>/dev/null; do
        attempt=$((attempt + 1))
        if [ $attempt -ge $max_attempts ]; then
            echo "MySQL is unavailable after $max_attempts attempts - exiting"
            exit 1
        fi
        echo "MySQL is unavailable - sleeping (attempt $attempt/$max_attempts)"
        sleep 3
    done
    
    echo "MySQL is up - continuing..."
else
    echo "ERROR: DB_HOST is not set! Check Railway environment variables."
    exit 1
fi

# Clear Laravel caches (skip cache:clear karena butuh DB)
echo "Clearing caches..."
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
# SKIP: php artisan cache:clear (butuh DB connection)

# Run migrations
echo "Running migrations..."
php artisan migrate --force --no-interaction || {
    echo "Migration failed!"
    exit 1
}

# Cache configuration for production
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions
echo "Setting permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "Starting Apache..."
exec apache2-foreground