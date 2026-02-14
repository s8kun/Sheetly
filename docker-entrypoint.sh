#!/bin/bash

# Install dependencies if vendor folder doesn't exist
if [ ! -d "vendor" ]; then
    composer install --no-interaction --optimize-autoloader
fi

# Run migrations and seeders (Force because it's production)
# We can skip db:monitor if it hangs on Render, but let's keep it if your DB is ready
php artisan migrate --force

# Start Apache
apache2-foreground
