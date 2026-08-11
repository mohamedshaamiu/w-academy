<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $username = strtoupper(trim(preg_replace('/\s+/', '', $validated['username'])));
        $key = $this->throttleKey($username, $request);

        // SPEC.md §8.1: 5 failed attempts per username per minute.
        if (RateLimiter::tooManyAttempts($key, 5)) {
            abort(429, __('auth.throttle', ['seconds' => RateLimiter::availableIn($key)]));
        }

        $user = User::where('username', $username)->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            RateLimiter::hit($key);

            throw ValidationException::withMessages(['username' => __('auth.failed')]);
        }

        if (! $user->is_active) {
            RateLimiter::hit($key);

            throw ValidationException::withMessages(['username' => __('auth.inactive')]);
        }

        RateLimiter::clear($key);

        $user->forceFill(['last_login_at' => now()])->save();

        return response()->json([
            'token' => $user->createToken('mobile')->plainTextToken,
            'user' => new UserResource($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => __('common.api.logged_out')], 200);
    }

    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    /** Mirrors the web login key (LoginRequest::throttleKey). */
    private function throttleKey(string $username, Request $request): string
    {
        return 'api|'.Str::transliterate(Str::lower($username).'|'.$request->ip());
    }
}
