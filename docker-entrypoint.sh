#!/bin/bash

# Install dependencies if vendor folder doesn't exist
if [ ! -d "vendor" ]; then
    composer install --no-interaction --optimize-autoloader
fi

# Wait for database to be ready
echo "Waiting for database..."
until php artisan db:monitor; do
  >&2 echo "Database is unavailable - sleeping"
  sleep 2
done

# Run migrations and seeders
php artisan migrate --force
php artisan db:seed --force

# Start PHP-FPM
php-fpm
