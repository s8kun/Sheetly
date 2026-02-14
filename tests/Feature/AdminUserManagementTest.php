<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can list users', function () {
    $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
    User::factory()->count(3)->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/admin/users');

    $response->assertStatus(200)
        ->assertJsonCount(4, 'data'); // 1 admin + 3 users
});

test('admin can create user', function () {
    $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/admin/users', [
            'name' => 'New Admin Created User',
            'email' => 'newuser@uob.edu.ly',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'student',
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('user.email', 'newuser@uob.edu.ly');

    $this->assertDatabaseHas('users', [
        'email' => 'newuser@uob.edu.ly',
        'role' => 'student',
    ]);
});

test('admin can update user', function () {
    $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
    $user = User::factory()->create(['name' => 'Old Name']);

    $response = $this->actingAs($admin, 'sanctum')
        ->patchJson("/api/admin/users/{$user->id}", [
            'name' => 'New Name',
        ]);

    $response->assertStatus(200)
        ->assertJsonPath('user.name', 'New Name');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'New Name',
    ]);
});

test('admin can delete user', function () {
    $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
    $user = User::factory()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->deleteJson("/api/admin/users/{$user->id}");

    $response->assertStatus(200);

    $this->assertDatabaseMissing('users', [
        'id' => $user->id,
    ]);
});

test('non-admin cannot manage users', function () {
    $user = User::factory()->create(['role' => 'student', 'email_verified_at' => now()]);
    $otherUser = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/admin/users');

    $response->assertStatus(403);

    $response = $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/admin/users/{$otherUser->id}");

    $response->assertStatus(403);
});
