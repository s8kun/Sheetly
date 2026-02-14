<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(): JsonResponse
    {
        $users = User::paginate(15);

        return response()->json($users);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['password'] = Hash::make($validated['password']);
        $validated['email_verified_at'] = now(); // Admin created users are verified by default

        $user = User::create($validated);

        return response()->json([
            'message' => 'تم إنشاء حساب المستخدم بنجاح.',
            'user' => $user,
        ], 201);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): JsonResponse
    {
        return response()->json($user);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $validated = $request->validated();

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'message' => 'تم تحديث بيانات المستخدم بنجاح.',
            'user' => $user,
        ]);
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user): JsonResponse
    {
        // Prevent admin from deleting themselves if needed, or just allow it.
        // Usually, it's better to prevent self-deletion for the last admin.

        if (auth()->id() === $user->id) {
            return response()->json(['message' => 'لا يمكنك حذف حسابك الخاص من هنا.'], 403);
        }

        $user->delete();

        return response()->json([
            'message' => 'تم حذف حساب المستخدم بنجاح.',
        ]);
    }
}
