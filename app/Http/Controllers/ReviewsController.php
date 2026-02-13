<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\YandexReviewsService;
use Illuminate\Http\JsonResponse;

/**
 * Reviews: API list (from Yandex URL in settings).
 */
class ReviewsController extends Controller
{
    /**
     * @param  YandexReviewsService  $reviews  Reviews fetch/parse service
     */
    public function __construct(
        private YandexReviewsService $reviews
    ) {}

    /**
     * List reviews for the current Yandex URL (API).
     */
    public function list(): JsonResponse
    {
        return response()->json($this->reviews->getReviewsForApp(YandexReviewsService::MAX_REVIEWS_LIMIT));
    }
}
