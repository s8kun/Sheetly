<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;

uses(RefreshDatabase::class);

test('login is rate limited', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password'),
        'email_verified_at' => now(),
    ]);

    // Send 5 requests (the limit)
    foreach (range(1, 5) as $i) {
        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertStatus(401);
    }

    // The 6th request should be rate limited
    $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertStatus(429);
});

test('otp routes are rate limited', function () {
    // Send 3 requests (the limit)
    foreach (range(1, 3) as $i) {
        $response = $this->postJson('/api/register/verify', [
            'email' => 'test@uob.edu.ly',
            'code' => '1234',
        ]);
        
        // Ensure it's not rate limited yet
        expect($response->status())->not->toBe(429);
    }

    // The 4th request should be rate limited
    $this->postJson('/api/register/verify', [
        'email' => 'test@uob.edu.ly',
        'code' => '1234',
    ])->assertStatus(429);
});
