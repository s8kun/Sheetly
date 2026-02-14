<?php
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/debug-smtp', function () {
    // 1. تنظيف الكاش عشان يقرأ التعديلات الجديدة من Render
    Artisan::call('config:clear');

    // 2. عرض الإعدادات الحالية (بدون الباسوورد طبعاً)
    return [
        'status' => 'Config Cleared!',
        'mailer' => Config::get('mail.default'),
        'host' => Config::get('mail.mailers.smtp.host'),
        'port' => Config::get('mail.mailers.smtp.port'),
        'username' => Config::get('mail.mailers.smtp.username'),
        'encryption' => Config::get('mail.mailers.smtp.encryption'),
        // لو هذا طلع NULL معناها Render مش قاري المتغير من الأساس
        'from_address' => Config::get('mail.from.address'),
    ];
});
