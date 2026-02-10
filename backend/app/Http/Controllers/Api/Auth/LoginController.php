<?php

namespace App\Http\Controllers\Api\Auth;

use App\Domain\Auth\Services\AuthService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function __construct(private readonly AuthService $authService)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $user = $this->authService->login($request);

        return response()->json(['user' => $user]);
    }
}


