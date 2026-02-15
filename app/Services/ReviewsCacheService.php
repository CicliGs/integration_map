<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Contracts\Cache\Repository as CacheRepository;

/**
 * Cache layer for Yandex reviews data. Used by YandexReviewsService.
 */
class ReviewsCacheService
{
    public function __construct(
        private CacheRepository $cache
    ) {}

    /**
     * Get value by key; if missing, run loader, store with TTL and return.
     *
     * @param  callable(): array  $loader  Called on cache miss
     * @return array
     */
    public function get(string $key, callable $loader, int $ttlSeconds): array
    {
        return $this->cache->remember($key, $ttlSeconds, $loader);
    }

    /**
     * Remove value by key.
     */
    public function forget(string $key): void
    {
        $this->cache->forget($key);
    }
}
