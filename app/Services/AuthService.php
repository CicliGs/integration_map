<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Authentication: login, logout, register, current user.
 */
class AuthService
{
    /**
     * Attempt to authenticate; on success regenerates session and returns user.
     *
     * @param  array{email: string, password: string}  $credentials
     * @return Authenticatable|null  The user on success, null on failure.
     */
    public function login(array $credentials, bool $remember, Request $request): ?Authenticatable
    {
        if (! Auth::attempt($credentials, $remember)) {
            Log::warning('Login failed', ['email' => $credentials['email'] ?? null]);

            return null;
        }

        Log::info('Login success', ['email' => $credentials['email']]);
        $request->session()->regenerate();

        return Auth::user();
    }

    /**
     * Log the user out and invalidate the session.
     */
    public function logout(Request $request): void
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    /**
     * Register a new user, log in and regenerate session.
     *
     * @param  array{name: string, email: string, password: string}  $data
     */
    public function register(array $data, Request $request): User
    {
        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);
        Auth::login($user);
        $request->session()->regenerate();

        return $user;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return Authenticatable|null
     */
    public function user(): ?Authenticatable
    {
        return Auth::user();
    }
}
