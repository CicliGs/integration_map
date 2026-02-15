<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Register a new user (API). Delegates to AuthService.
 */
class RegisterController extends Controller
{
    public function __construct(
        private AuthService $auth
    ) {}

    /**
     * Register a new user and log in.
     */
    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $user = $this->auth->register($request->validated(), $request);

        return response()->json(['user' => $user->toArray()], Response::HTTP_CREATED);
    }
}
