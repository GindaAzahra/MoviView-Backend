#!/bin/bash

set -e

echo "Starting Laravel application..."

# Check if .env exists
if [ ! -f /var/www/html/.env ]; then
    echo "ERROR: .env file not found!"
    exit 1
fi

# Show DB config (for debugging)
echo "Checking database configuration..."
DB_HOST=$(php -r "echo env('DB_HOST', 'not set');")
DB_DATABASE=$(php -r "echo env('DB_DATABASE', 'not set');")
echo "DB_HOST: $DB_HOST"
echo "DB_DATABASE: $DB_DATABASE"

if [ "$DB_HOST" = "not set" ] || [ "$DB_DATABASE" = "not set" ]; then
    echo "ERROR: Database configuration not found in .env"
    cat /var/www/html/.env
    exit 1
fi

# Wait for MySQL
echo "Waiting for MySQL at $DB_HOST..."
max_attempts=30
attempt=0

DB_PORT=$(php -r "echo env('DB_PORT', '3306');")
DB_USERNAME=$(php -r "echo env('DB_USERNAME', 'root');")
DB_PASSWORD=$(php -r "echo env('DB_PASSWORD', '');")

until php -r "
try { 
    new PDO('mysql:host=$DB_HOST;port=$DB_PORT', '$DB_USERNAME', '$DB_PASSWORD'); 
    echo 'Connected'; 
    exit(0);
} catch(PDOException \$e) { 
    exit(1); 
}" 2>/dev/null; do
    attempt=$((attempt + 1))
    if [ $attempt -ge $max_attempts ]; then
        echo "MySQL unavailable after $max_attempts attempts"
        exit 1
    fi
    echo "Waiting for MySQL... (attempt $attempt/$max_attempts)"
    sleep 3
done

echo "MySQL is ready!"

# Clear caches
echo "Clearing caches..."
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Run migrations
echo "Running migrations..."
php artisan migrate --force --no-interaction

# Cache for production
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
echo "Setting permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "Starting Apache..."
exec apache2-foreground