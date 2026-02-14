#!/bin/bash

# تنظيف الكاش
php artisan config:clear
php artisan cache:clear

# إصلاح الصلاحيات
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# محاولة الاتصال بقاعدة البيانات قبل تشغيل الـ Migration
echo "Checking database connection..."
if php artisan db:monitor > /dev/null 2>&1; then
    echo "Database is reachable. Running migrations..."
    php artisan migrate --force
else
    echo "Database is NOT reachable (Host: $DB_HOST). Skipping migrations for now so the app can start."
fi

# تشغيل Apache
echo "Starting Apache..."
exec apache2-foreground
