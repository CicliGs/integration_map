<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Authentication: login, logout, current user.
 */
class AuthService
{
    /**
     * Attempt to authenticate with the given credentials.
     *
     * @param  array{email: string, password: string}  $credentials
     * @return Authenticatable|null  The user on success, null on failure.
     */
    public function attempt(array $credentials, bool $remember = false): ?Authenticatable
    {
        if (! Auth::attempt($credentials, $remember)) {
            Log::warning('Login failed', ['email' => $credentials['email'] ?? null]);

            return null;
        }

        Log::info('Login success', ['email' => $credentials['email']]);

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
     * Get the currently authenticated user.
     *
     * @return Authenticatable|null
     */
    public function user(): ?Authenticatable
    {
        return Auth::user();
    }
}
