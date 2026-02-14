<?php
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
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
Route::get('/test-email', function () {
    try {
        Mail::raw('هذا اختبار من سيرفر Render', function ($message) {
            $message->to('uob.sheetly@gmail.com') // ابعت لنفسك عشان تتأكد
            ->subject('تجربة SMTP');
        });

        return 'تم الإرسال بنجاح! المشكلة انحلت.';
    } catch (\Exception $e) {
        // هذا السطر حيعطينا الزبدة
        return response()->json([
            'error_message' => $e->getMessage(),
            'error_code' => $e->getCode(),
        ], 500);
    }
});
