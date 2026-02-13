<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Setting;
use App\Repositories\Contracts\SettingRepositoryInterface;
use Illuminate\Support\Facades\DB;

/**
 * Eloquent implementation of settings storage (single row, Yandex URL).
 */
class SettingRepository implements SettingRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getYandexReviewsUrl(): ?string
    {
        $setting = Setting::query()->first();

        return $setting?->yandex_reviews_url;
    }

    /**
     * {@inheritdoc}
     */
    public function updateOrCreateYandexReviewsUrl(string $url): string
    {
        return DB::transaction(function () use ($url): string {
            $setting = Setting::query()->lockForUpdate()->first();

            if ($setting === null) {
                $setting = Setting::query()->create(['yandex_reviews_url' => $url]);
            } else {
                $setting->update(['yandex_reviews_url' => $url]);
            }

            return (string) $setting->yandex_reviews_url;
        });
    }
}
