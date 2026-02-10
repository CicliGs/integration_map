<?php

namespace App\Http\Controllers\Api\Auth;

use App\Domain\Auth\Services\AuthService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function __construct(private readonly AuthService $authService)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $this->authService->logout($request);

        return response()->json(['message' => 'Вы вышли из системы']);
    }
}


