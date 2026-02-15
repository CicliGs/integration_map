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

    /** Max reviews to fetch for the app (all from the page / multiple pages). */
    public const MAX_REVIEWS_LIMIT = 2000;

    /** Default items per page for paginated response. */
    public const DEFAULT_PER_PAGE = 50;

    /** Maximum allowed per_page for paginated response. */
    public const MAX_PER_PAGE = 100;

    /**
     * @param  ClientInterface  $client  HTTP client for fetching pages
     * @param  YandexPageParser  $parser  Parser for Yandex Maps HTML
     * @param  SettingRepositoryInterface  $settings  Repository for stored Yandex URL
     */
    public function __construct(
        private ClientInterface $client,
        private YandexPageParser $parser,
        private SettingRepositoryInterface $settings
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
     *
     * @return array{company: array{name: mixed, rating: mixed, reviews_count: mixed, ratings_count: mixed}, reviews: array}
     */
    public function getReviewsForApp(int $limit = self::DEFAULT_LIMIT): array
    {
        $url = $this->settings->getYandexReviewsUrl();
        if ($url === null || $url === '') {
            return $this->emptyResult();
        }

        $data = $this->fetchReviews($url, $limit);
        Log::debug('Yandex reviews parsed', $data);

        return $data;
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
