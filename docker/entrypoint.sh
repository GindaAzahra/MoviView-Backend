#!/bin/bash
set -e

# Change Apache port to Railway/Render dynamic PORT
if [ ! -z "$PORT" ]; then
    echo "--- Configuring Apache to listen on port $PORT ---"
    sed -i "s/80/$PORT/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf
fi

# Debug: Check if DB variables are present (hiding values)
echo "--- Environment Check ---"
echo "DB_CONNECTION: ${DB_CONNECTION:-[Not Set - Defaulting to mysql]}"
echo "DB_HOST: ${DB_HOST:-[Not Set]} / MYSQLHOST: ${MYSQLHOST:-[Not Set]}"
echo "DB_DATABASE: ${DB_DATABASE:-[Not Set]} / MYSQLDATABASE: ${MYSQLDATABASE:-[Not Set]}"
echo "MYSQL_URL: ${MYSQL_URL:+[Detected - Will use for connection]}"

# 1. Force clear any cached config
echo "--- Clearing Laravel configuration cache ---"
php artisan config:clear --ansi

# 2. Run migrations
echo "--- Running database migrations ---"
php artisan migrate --force -vvv --ansi || {
    echo "--- !!! MIGRATION FAILED !!! ---"
    echo "Please check if your MySQL service is running and connected."
    exit 1
}

# 3. Create fresh cache for production performance
echo "--- Caching configuration and routes ---"
php artisan config:cache --ansi
php artisan route:cache --ansi
php artisan view:cache --ansi

# Start Apache in the foreground
echo "--- Starting Apache ---"
exec apache2-foreground
