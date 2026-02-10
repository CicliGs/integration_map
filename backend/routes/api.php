<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Yandex\YandexReviewsController;
use App\Http\Controllers\Yandex\YandexSettingsController;
use Illuminate\Support\Facades\Route;

// Аутентификация
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:web')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/yandex-settings', [YandexSettingsController::class, 'show']);
    Route::post('/yandex-settings', [YandexSettingsController::class, 'store']);

    Route::get('/yandex-reviews', [YandexReviewsController::class, 'index']);
});

<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Yandex\YandexReviewsController;
use App\Http\Controllers\Api\Yandex\YandexSettingsController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', LoginController::class);

Route::middleware('auth')->group(static function (): void {
    Route::post('/auth/logout', LogoutController::class);

    Route::get('/yandex/settings', [YandexSettingsController::class, 'show']);
    Route::post('/yandex/settings', [YandexSettingsController::class, 'store']);

    Route::get('/yandex/reviews', YandexReviewsController::class);
});


