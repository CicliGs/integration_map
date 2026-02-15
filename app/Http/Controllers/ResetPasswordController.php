<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ResetPasswordRequest;
use App\Services\PasswordResetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;

/**
 * Reset password (API). Delegates to PasswordResetService.
 */
class ResetPasswordController extends Controller
{
    public function __construct(
        private PasswordResetService $password
    ) {}

    /**
     * Reset the user's password.
     */
    public function __invoke(ResetPasswordRequest $request): JsonResponse
    {
        $status = $this->password->reset($request->only('email', 'password', 'password_confirmation', 'token'));

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json(['message' => __('passwords.token')], 422);
        }

        return response()->json(['message' => __('passwords.reset')]);
    }
}
