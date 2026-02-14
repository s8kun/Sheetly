#!/bin/bash

# تأكد من الصلاحيات في وقت التشغيل (مهم جداً لـ Render)
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# مسح الكاش القديم لضمان قراءة متغيرات البيئة من Render
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# تشغيل الـ Discovery
php artisan package:discover --ansi

# تشغيل التهجير (Migration)
# تأكد أنك وضعت متغيرات قاعدة البيانات في Render Dashboard
php artisan migrate --force

# تشغيل Apache
echo "Starting Apache..."
exec apache2-foreground
