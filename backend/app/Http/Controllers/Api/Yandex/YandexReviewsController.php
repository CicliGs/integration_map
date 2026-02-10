<?php

namespace App\Http\Controllers\Api\Yandex;

use App\Domain\Yandex\Repositories\YandexSettingsRepository;
use App\Domain\Yandex\Services\YandexReviewsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class YandexReviewsController extends Controller
{
    public function __construct(
        private readonly YandexSettingsRepository $settingsRepository,
        private readonly YandexReviewsService $reviewsService,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $setting = $this->settingsRepository->getByUser($request->user());

        if ($setting === null) {
            return response()->json([
                'summary' => null,
                'reviews' => [],
            ]);
        }

        $result = $this->reviewsService->getReviewsForSetting($setting);

        return response()->json([
            'summary' => [
                'rating' => $result['summary']->rating,
                'reviews_count' => $result['summary']->reviewsCount,
            ],
            'reviews' => array_map(static function ($review) {
                return [
                    'id' => $review->id,
                    'date' => $review->date,
                    'branch' => $review->branch,
                    'phone' => $review->phone,
                    'rating' => $review->rating,
                    'text' => $review->text,
                ];
            }, $result['reviews']),
        ]);
    }
}


