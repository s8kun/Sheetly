<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\SheetController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Scheduler Trigger (For Render/UptimeRobot)
Route::get('/run-scheduler/{token}', function ($token) {
    if ($token !== config('app.scheduler_token')) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    Artisan::call('schedule:run');

    return response()->json([
        'message' => 'Schedule executed successfully',
        'output' => Artisan::output(),
    ]);
});

// Authentication
Route::controller(AuthController::class)->group(function () {
    Route::post('/register', 'register')->middleware('throttle:otp');
    Route::post('/register/verify', 'verifyRegistration')->middleware('throttle:otp');
    Route::post('/resend-otp', 'resendOtp')->middleware('throttle:otp');
    Route::post('/login', 'login')->middleware('throttle:login');
});

// Public Routes
Route::get('/subjects', [SubjectController::class, 'index']);
Route::get('/subjects/{subject}', [SubjectController::class, 'show']);
Route::get('/subjects/{subject}/chapters/{chapterNumber}', [SubjectController::class, 'showChapter']);
Route::get('/sheets/{sheet}', [SheetController::class, 'show']); // مسار عرض الشيت الواحد (مسموح للكل كبيانات)
Route::get('/sheets/{sheet}/download', [SheetController::class, 'download']);

Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->middleware('throttle:otp');
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->middleware('throttle:otp');

// Protected Routes (User & Admin)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // User's Sheet Management
    Route::get('/my-sheets', [SheetController::class, 'mySheets']);
    Route::post('/sheets/upload', [SheetController::class, 'store']);
    Route::delete('/sheets/{sheet}', [SheetController::class, 'destroy']); // المستخدم يحذف شيتاته، والأدمن يحذف أي شيت

    // Admin Only Area
    Route::middleware('admin')->prefix('admin')->group(function () {
        // Sheet Moderation
        Route::get('/sheets/pending', [SheetController::class, 'pendingSheets']);
        Route::patch('/sheets/{sheet}/approve', [SheetController::class, 'approve']);
        Route::patch('/sheets/{sheet}/reject', [SheetController::class, 'reject']);

        // Subject Management
        Route::post('/subjects', [SubjectController::class, 'store']);
        Route::match(['put', 'patch'], '/subjects/{subject}', [SubjectController::class, 'update']);
        Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy']);

        // User Management
        Route::apiResource('users', UserController::class);
    });
});
