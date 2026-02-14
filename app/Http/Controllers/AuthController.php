<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Models\Otp;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Register a new user - Stage 1: Create user and send OTP.
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users',
                'ends_with:@uob.edu.ly',
            ],
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.ends_with' => 'يجب استخدام البريد الجامعي الخاص بجامعة بنغازي (@uob.edu.ly) فقط.',
            'email.unique' => 'هذا البريد مسجل مسبقاً.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Send OTP for email verification
        $code = rand(1000, 9999);
        Otp::updateOrCreate(
            ['email' => $request->email],
            [
                'code' => $code,
                'expires_at' => Carbon::now()->addMinutes(5),
                'created_at' => Carbon::now(),
            ]
        );

        try {
            Mail::to($request->email)->send(new OtpMail($code));
        } catch (\Exception $e) {
            return response()->json(['message' => 'تم إنشاء الحساب ولكن فشل إرسال الرمز.'], 500);
        }

        return response()->json([
            'message' => 'تم إنشاء الحساب بنجاح. يرجى تأكيد حسابك باستخدام رمز التحقق المرسل لبريدك.',
            'user' => $user,
        ], 201);
    }

    /**
     * Verify Registration OTP.
     */
    public function verifyRegistration(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|numeric',
        ]);

        $otp = Otp::where('email', $request->email)
            ->where('code', $request->code)
            ->first();

        if (! $otp) {
            return response()->json(['message' => 'الرمز غير صحيح.'], 400);
        }

        if ($otp->expires_at->isPast()) {
            return response()->json(['message' => 'انتهت صلاحية الرمز.'], 400);
        }

        // Clean up OTP
        $otp->delete();

        $user = User::where('email', $request->email)->firstOrFail();

        // Mark as verified if you want to use email_verified_at
        $user->email_verified_at = now();
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'تم تأكيد الحساب بنجاح.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    /**
     * Resend OTP to user.
     */
    public function resendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->email_verified_at) {
            return response()->json(['message' => 'هذا الحساب مفعّل مسبقاً.'], 400);
        }

        $code = rand(1000, 9999);
        Otp::updateOrCreate(
            ['email' => $request->email],
            [
                'code' => $code,
                'expires_at' => Carbon::now()->addMinutes(5),
                'created_at' => Carbon::now(),
            ]
        );

        try {
            Mail::to($request->email)->send(new OtpMail($code));
        } catch (\Exception $e) {
            return response()->json(['message' => 'فشل في إرسال الرمز.'], 500);
        }

        return response()->json(['message' => 'تم إعادة إرسال رمز التحقق بنجاح.']);
    }

    /**
     * Login user and create token (Standard Login).
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'بيانات تسجيل الدخول غير صحيحة.',
            ], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();

        // Optional: Check if email is verified
        if (! $user->email_verified_at) {
            return response()->json(['message' => 'يرجى تأكيد الحساب أولاً.'], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'تم تسجيل الدخول بنجاح.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    /**
     * Logout user (revoke token).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'تم تسجيل الخروج بنجاح.',
        ]);
    }
}
