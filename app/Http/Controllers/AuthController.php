<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Login, logout, current user (API).
 */
class AuthController extends Controller
{
    /**
     * @param  AuthService  $auth  Authentication service
     */
    public function __construct(
        private AuthService $auth
    ) {}

    /**
     * Authenticate user and create session.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();
        $user = $this->auth->attempt($credentials, $request->boolean('remember'));

        if ($user === null) {
            throw new ApiException('Неверный email или пароль.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $request->session()->regenerate();

        return response()->json(['user' => $user->toArray()]);
    }

    /**
     * Log out and invalidate session.
     */
    public function logout(Request $request): JsonResponse
    {
        $this->auth->logout($request);

        return response()->json(['message' => 'Выход выполнен']);
    }

    /**
     * Return the currently authenticated user.
     */
    public function user(): JsonResponse
    {
        $user = $this->auth->user();

        return response()->json($user?->toArray());
    }
}
