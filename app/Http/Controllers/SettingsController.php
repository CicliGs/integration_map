<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdateYandexSettingsRequest;
use App\Services\SettingsService;
use App\Services\YandexReviewsService;
use Illuminate\Http\JsonResponse;

/**
 * Yandex settings: API show/update.
 */
class SettingsController extends Controller
{
    /**
     * @param  SettingsService  $settings  Settings service
     */
    public function __construct(
        private SettingsService $settings,
        private YandexReviewsService $reviews
    ) {}

    /**
     * Get Yandex settings (API).
     */
    public function show(): JsonResponse
    {
        return response()->json([
            'yandex_reviews_url' => $this->settings->getYandexReviewsUrl(),
        ]);
    }

    /**
     * Update Yandex reviews URL (API).
     */
    public function update(UpdateYandexSettingsRequest $request): JsonResponse
    {
        $url = $request->validated('yandex_reviews_url');
        $result = $this->settings->updateYandexReviewsUrl($url);
        $this->reviews->clearReviewsCache($url);

        return response()->json($result);
    }
}
