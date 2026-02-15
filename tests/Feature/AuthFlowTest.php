<?php

use App\Mail\OtpMail;
use App\Mail\ResetPasswordMail;
use App\Models\Otp;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
});

test('it sends otp during registration', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'test@uob.edu.ly',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('users', ['email' => 'test@uob.edu.ly']);
    $this->assertDatabaseHas('otps', ['email' => 'test@uob.edu.ly']);
    Mail::assertSent(OtpMail::class);
});

test('it rejects registration with non-uob email', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'test@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email']);
});

test('it recycles unverified account during registration', function () {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'test@uob.edu.ly',
        'password' => Hash::make('oldpassword'),
        'email_verified_at' => null,
    ]);

    $response = $this->postJson('/api/register', [
        'name' => 'New Name',
        'email' => 'test@uob.edu.ly',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertStatus(201);

    $user->refresh();
    expect($user->name)->toBe('New Name');
    expect(Hash::check('newpassword123', $user->password))->toBeTrue();

    $this->assertDatabaseHas('otps', ['email' => 'test@uob.edu.ly']);
    Mail::assertSent(OtpMail::class);
});

test('it rejects registration if email is already verified', function () {
    User::factory()->create([
        'email' => 'verified@uob.edu.ly',
        'email_verified_at' => now(),
    ]);

    $response = $this->postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'verified@uob.edu.ly',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email']);
    $response->assertJsonFragment(['هذا البريد مسجل مسبقاً.']);
});

test('it can verify registration with otp', function () {
    $user = User::factory()->create([
        'email' => 'test@uob.edu.ly',
        'email_verified_at' => null,
    ]);

    $otp = Otp::create([
        'email' => $user->email,
        'code' => '1234',
        'expires_at' => Carbon::now()->addMinutes(5),
    ]);

    $response = $this->postJson('/api/register/verify', [
        'email' => $user->email,
        'code' => '1234',
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure(['access_token', 'user']);
    $this->assertNotNull($user->refresh()->email_verified_at);
    $this->assertDatabaseMissing('otps', ['email' => $user->email]);
});

test('it can login with standard credentials after verification', function () {
    $user = User::factory()->create([
        'email' => 'test@uob.edu.ly',
        'password' => Hash::make('password123'),
        'email_verified_at' => now(),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure(['access_token', 'user']);
});

test('it cannot login if not verified', function () {
    $user = User::factory()->create([
        'email' => 'test@uob.edu.ly',
        'password' => Hash::make('password123'),
        'email_verified_at' => null,
    ]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $response->assertStatus(403);
    $response->assertJson(['message' => 'يرجى تأكيد الحساب أولاً.']);
});

test('it can send password reset link', function () {
    $user = User::factory()->create(['email' => 'test@uob.edu.ly']);

    $response = $this->postJson('/api/forgot-password', [
        'email' => $user->email,
    ]);

    $response->assertStatus(200);
    Mail::assertSent(ResetPasswordMail::class);
});
