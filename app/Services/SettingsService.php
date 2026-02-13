<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\SettingRepositoryInterface;

/**
 * Application settings (e.g. Yandex reviews URL) read/update via repository.
 */
class SettingsService
{
    /**
     * @param  SettingRepositoryInterface  $settings  Repository for settings storage
     */
    public function __construct(
        private SettingRepositoryInterface $settings
    ) {}

    /**
     * Get the Yandex reviews URL from settings (for API/view).
     */
    public function getYandexReviewsUrl(): ?string
    {
        return $this->settings->getYandexReviewsUrl();
    }

    /**
     * Update or create the Yandex reviews URL. Returns the new value for response.
     *
     * @return array{yandex_reviews_url: string}
     */
    public function updateYandexReviewsUrl(string $url): array
    {
        $stored = $this->settings->updateOrCreateYandexReviewsUrl($url);

        return ['yandex_reviews_url' => $stored];
    }
}
