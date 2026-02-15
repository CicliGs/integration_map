<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\SettingRepositoryInterface;
use App\Services\Yandex\YandexPageParser;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

/**
 * Fetches and parses Yandex Maps reviews page; resolves settings via repository.
 */
class YandexReviewsService
{
    /** Default number of reviews to fetch when limit is not specified. */
    public const DEFAULT_LIMIT = 10;

    /** Max reviews to fetch for the app (aim for all from the place). */
    public const MAX_REVIEWS_LIMIT = 15000;

    /** Max number of Yandex "pages" to request when trying to get more reviews. */
    private const MAX_PAGES_TO_FETCH = 150;

    /** Cache TTL for full Yandex result (seconds). Same data for all pages and refreshes. */
    private const CACHE_TTL_SECONDS = 900;

    /** Default items per page for paginated response. */
    public const DEFAULT_PER_PAGE = 50;

    /** Maximum allowed per_page for paginated response. */
    public const MAX_PER_PAGE = 100;

    /**
     * @param  ClientInterface  $client  HTTP client for fetching pages
     * @param  YandexPageParser  $parser  Parser for Yandex Maps HTML
     * @param  SettingRepositoryInterface  $settings  Repository for stored Yandex URL
     * @param  ReviewsCacheService  $cache  Cache for full reviews result
     */
    public function __construct(
        private ClientInterface $client,
        private YandexPageParser $parser,
        private SettingRepositoryInterface $settings,
        private ReviewsCacheService $cache
    ) {}

    /**
     * Fetch and parse reviews from a Yandex Maps page URL.
     *
     * @return array{company: array{name: mixed, rating: mixed, reviews_count: mixed, ratings_count: mixed}, reviews: array}
     */
    public function fetchReviews(string $url, int $limit = self::DEFAULT_LIMIT): array
    {
        $limit = $limit > 0 ? $limit : self::DEFAULT_LIMIT;

        $html = $this->fetchHtml($url);
        if ($html === null) {
            return $this->emptyResult();
        }

        $data = $this->parser->parse($html, $limit);

        if (empty($data['company']['name']) && $url !== '') {
            $data['company']['name'] = $this->companyNameFromUrl($url);
        }

        return $data;
    }

    /**
     * Get reviews using the URL stored in settings.
     * Result is cached so all requests (any page, refresh) see the same dataset and load fast.
     * Tries multiple "pages" (URL + ?page=2, ?page=3, …) and merges unique reviews.
     *
     * @return array{company: array{name: mixed, rating: mixed, reviews_count: mixed, ratings_count: mixed}, reviews: array}
     */
    public function getReviewsForApp(int $limit = self::DEFAULT_LIMIT): array
    {
        $url = $this->settings->getYandexReviewsUrl();
        if ($url === null || $url === '') {
            return $this->emptyResult();
        }

        $cacheKey = 'yandex_reviews_' . md5($url);

        $data = $this->cache->get($cacheKey, function () use ($url, $limit) {
            $result = $this->fetchReviewsAllPages($url, $limit);
            Log::debug('Yandex reviews fetched and cached', ['reviews_count' => count($result['reviews'] ?? [])]);
            return $result;
        }, self::CACHE_TTL_SECONDS);

        return $data;
    }

    /**
     * Clear cached reviews for a URL (e.g. after user changes the link in settings).
     */
    public function clearReviewsCache(string $url): void
    {
        $this->cache->forget('yandex_reviews_' . md5($url));
    }

    /**
     * Fetch reviews from base URL and optional ?page=2, ?page=3, …; merge and dedupe.
     *
     * @return array{company: array{name: mixed, rating: mixed, reviews_count: mixed, ratings_count: mixed}, reviews: array}
     */
    private function fetchReviewsAllPages(string $baseUrl, int $limit): array
    {
        $company = null;
        $seen = [];
        $allReviews = [];
        $separator = str_contains($baseUrl, '?') ? '&' : '?';

        for ($page = 1; $page <= self::MAX_PAGES_TO_FETCH; $page++) {
            $url = $page === 1 ? $baseUrl : $baseUrl . $separator . 'page=' . $page;
            $data = $this->fetchReviews($url, $limit);

            if ($company === null && ! empty($data['company'])) {
                $company = $data['company'];
            }
            if (empty($data['company']['name']) && $baseUrl !== '') {
                $data['company']['name'] = $this->companyNameFromUrl($baseUrl);
            }
            if ($company === null) {
                $company = $data['company'] ?? $this->emptyCompany();
            }

            $newCount = 0;
            foreach ($data['reviews'] ?? [] as $review) {
                $key = ($review['author'] ?? '') . '|' . ($review['date'] ?? '') . '|' . ($review['text'] ?? '');
                if (! isset($seen[$key])) {
                    $seen[$key] = true;
                    $allReviews[] = $review;
                    $newCount++;
                }
            }

            if ($newCount === 0 || count($allReviews) >= $limit) {
                break;
            }
        }

        $reviews = array_slice($allReviews, 0, $limit);
        foreach ($reviews as $i => $review) {
            $reviews[$i]['id'] = $i;
        }

        return [
            'company' => $company ?? $this->emptyCompany(),
            'reviews' => $reviews,
        ];
    }

    /**
     * Get paginated reviews for the app (URL from settings).
     *
     * @param  int  $page  Page number (1-based), clamped to valid range
     * @param  int  $perPage  Items per page (clamped to 1..MAX_PER_PAGE)
     * @return array{company: array, reviews: array, meta: array{total: int, per_page: int, current_page: int, last_page: int}}
     */
    public function getReviewsForAppPaginated(int $page = 1, int $perPage = self::DEFAULT_PER_PAGE): array
    {
        $perPage = $this->clampPerPage($perPage);
        $data = $this->getReviewsForApp(self::MAX_REVIEWS_LIMIT);
        $allReviews = $data['reviews'] ?? [];
        $total = count($allReviews);
        $lastPage = $total > 0 ? (int) ceil($total / $perPage) : 1;
        $page = max(1, min($page, $lastPage));
        $offset = ($page - 1) * $perPage;
        $reviews = array_slice($allReviews, $offset, $perPage);

        return [
            'company' => $data['company'] ?? $this->emptyCompany(),
            'reviews' => $reviews,
            'meta' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => $lastPage,
            ],
        ];
    }

    /**
     * Clamp per_page to allowed range.
     */
    private function clampPerPage(int $value): int
    {
        if ($value < 1) {
            return self::DEFAULT_PER_PAGE;
        }
        if ($value > self::MAX_PER_PAGE) {
            return self::MAX_PER_PAGE;
        }
        return $value;
    }

    /**
     * Empty company structure for API response.
     *
     * @return array{name: null, rating: null, reviews_count: null, ratings_count: null}
     */
    private function emptyCompany(): array
    {
        return [
            'name' => null,
            'rating' => null,
            'reviews_count' => null,
            'ratings_count' => null,
        ];
    }

    /**
     * Extract organization name from Yandex Maps URL (e.g. .../org/name_slug/123/...).
     */
    public function companyNameFromUrl(string $url): ?string
    {
        if (preg_match('#/org/([^/?]+)#', $url, $m) !== 1) {
            return null;
        }
        $slug = trim($m[1]);
        if ($slug === '') {
            return null;
        }
        $name = str_replace('_', ' ', $slug);
        $name = preg_replace('/\s+/u', ' ', trim($name)) ?? '';
        return $name !== '' ? mb_convert_case($name, MB_CASE_TITLE, 'UTF-8') : null;
    }

    /**
     * Fetch raw HTML from the given URL. Returns null on failure or empty response.
     */
    private function fetchHtml(string $url): ?string
    {
        try {
            $response = $this->client->request('GET', $url);
        } catch (GuzzleException) {
            return null;
        }
        $body = (string) $response->getBody();
        return $body !== '' ? $body : null;
    }

    /**
     * Return empty company and reviews structure.
     *
     * @return array{company: array{name: null, rating: null, reviews_count: null, ratings_count: null}, reviews: array}
     */
    private function emptyResult(): array
    {
        return [
            'company' => [
                'name' => null,
                'rating' => null,
                'reviews_count' => null,
                'ratings_count' => null,
            ],
            'reviews' => [],
        ];
    }
}
