<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * Password reset: send link and reset password. Wraps Laravel Password facade.
 */
class PasswordResetService
{
    /**
     * Send password reset link to the given email.
     *
     * @return string  Status (e.g. Password::RESET_LINK_SENT)
     */
    public function sendResetLink(string $email): string
    {
        return Password::sendResetLink(['email' => $email]);
    }

    /**
     * Reset password for the given token/email.
     *
     * @param  array{email: string, password: string, password_confirmation: string, token: string}  $data
     * @return string  Status (e.g. Password::PASSWORD_RESET)
     */
    public function reset(array $data): string
    {
        return Password::reset($data, function ($user, $password) {
            $user->forceFill([
                'password' => $password,
                'remember_token' => Str::random(60),
            ])->save();
        });
    }
}
