<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    /**
     * Register a new user and log in.
     */
    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $user = User::query()->create(
            $request->safe()->only(['name', 'email', 'password'])
        );

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json(['user' => $user->toArray()], Response::HTTP_CREATED);
    }
}
