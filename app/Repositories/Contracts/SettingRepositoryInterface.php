<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

/**
 * Settings storage (Yandex reviews URL).
 */
interface SettingRepositoryInterface
{
    /**
     * Get the stored Yandex reviews URL, or null if not set.
     */
    public function getYandexReviewsUrl(): ?string;

    /**
     * Create or update the Yandex reviews URL. Returns the stored value.
     */
    public function updateOrCreateYandexReviewsUrl(string $url): string;
}
