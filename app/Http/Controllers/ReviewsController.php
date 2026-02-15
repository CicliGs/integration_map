<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\YandexReviewsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API controller for listing Yandex Maps reviews (from URL stored in settings).
 *
 * Returns paginated reviews with company summary and pagination meta.
 */
class ReviewsController extends Controller
{
    public function __construct(
        private YandexReviewsService $reviews
    ) {}

    /**
     * List reviews for the current Yandex URL, paginated.
     *
     * @param  Request  $request  Query: page (int), per_page (int, 1..100)
     * @return JsonResponse  { company, reviews, meta }
     */
    public function list(Request $request): JsonResponse
    {
        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', YandexReviewsService::DEFAULT_PER_PAGE);

        $result = $this->reviews->getReviewsForAppPaginated($page, $perPage);

        return response()->json($result);
    }
}
