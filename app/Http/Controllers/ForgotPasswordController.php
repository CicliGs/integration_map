<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordRequest;
use App\Services\PasswordResetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;

/**
 * Send password reset link (API). Delegates to PasswordResetService.
 */
class ForgotPasswordController extends Controller
{
    public function __construct(
        private PasswordResetService $password
    ) {}

    /**
     * Send password reset link to the given email.
     */
    public function __invoke(ForgotPasswordRequest $request): JsonResponse
    {
        $status = $this->password->sendResetLink($request->validated('email'));

        if ($status !== Password::RESET_LINK_SENT) {
            return response()->json(['message' => __('passwords.user')], 422);
        }

        return response()->json(['message' => __('passwords.sent')]);
    }
}
