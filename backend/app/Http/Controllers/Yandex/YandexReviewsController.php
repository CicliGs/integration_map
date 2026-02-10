<?php

namespace App\Http\Controllers\Yandex;

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

    public function index(Request $request): JsonResponse
    {
        $setting = $this->settingsRepository->getByUser($request->user());

        if (! $setting) {
            return response()->json([
                'summary' => null,
                'reviews' => [],
            ]);
        }

        $data = $this->reviewsService->getReviewsForSetting($setting);

        return response()->json([
            'summary' => [
                'rating' => $data['summary']->rating,
                'reviews_count' => $data['summary']->reviewsCount,
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
            }, $data['reviews']),
        ]);
    }
}


