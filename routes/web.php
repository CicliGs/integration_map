<?php

declare(strict_types=1);

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\ReviewsController;
use App\Http\Controllers\SettingsController;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::prefix('api')->group(function () {
    Route::get('/sanctum/csrf-cookie', CsrfCookieController::class)
        ->name('api.sanctum');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:login')
        ->name('api.login');

    Route::post('/register', RegisterController::class)
        ->middleware('throttle:api')
        ->name('api.register');

    Route::post('/forgot-password', ForgotPasswordController::class)
        ->middleware('throttle:forgot-password')
        ->name('api.password.email');

    Route::post('/reset-password', ResetPasswordController::class)
        ->middleware('throttle:reset-password')
        ->name('api.password.update');

    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
        Route::get('/user', [AuthController::class, 'user'])->name('api.user');
        Route::get('/settings/yandex', [SettingsController::class, 'show'])->name('api.settings.yandex.show');
        Route::post('/settings/yandex', [SettingsController::class, 'update'])->name('api.settings.yandex.update');
        Route::get('/reviews', [ReviewsController::class, 'list'])->name('api.reviews.list');
    });
});

Route::get('/login', function () {
    return view('app');
})->name('login');

Route::get('/register', function () {
    return view('app');
})->name('register');

Route::get('/settings', function () {
    return view('app');
})->name('settings.index');

Route::get('/reviews', function () {
    return view('app');
})->name('reviews.index');

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
