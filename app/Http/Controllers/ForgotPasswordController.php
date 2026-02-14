<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    // 1. Send Reset Link
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => [
                'required',
                'email',
                'exists:users,email',
                'ends_with:@uob.edu.ly',
            ],
        ], [
            'email.ends_with' => 'يجب استخدام البريد الجامعي الخاص بجامعة بنغازي فقط.',
        ]);

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($token),
                'created_at' => Carbon::now(),
            ]
        );

        // رابط فرونت اند (Next.js)
        $frontendUrl = config('app.frontend_url').'/reset-password?token='.$token.'&email='.$request->email;

        try {
            Mail::to($request->email)->send(new ResetPasswordMail($frontendUrl));
        } catch (\Exception $e) {
            return response()->json(['message' => 'فشل في إرسال البريد الإلكتروني.'], 500);
        }

        return response()->json(['message' => 'تم إرسال رابط إعادة تعيين كلمة المرور بنجاح.'], 200);
    }

    // 2. Reset Password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        $reset = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (! $reset || ! Hash::check($request->token, $reset->token)) {
            return response()->json(['message' => 'رابط غير صالح أو منتهي الصلاحية.'], 400);
        }

        // Check if token is older than 60 minutes (Laravel default)
        if (Carbon::parse($reset->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            return response()->json(['message' => 'انتهت صلاحية الرابط.'], 400);
        }

        $user = User::where('email', $request->email)->first();
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'تم تغيير كلمة المرور بنجاح.'], 200);
    }
}
