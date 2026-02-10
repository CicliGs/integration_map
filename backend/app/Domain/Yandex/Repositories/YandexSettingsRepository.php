<?php

namespace App\Domain\Yandex\Repositories;

use App\Models\User;
use App\Models\YandexSetting;

class YandexSettingsRepository
{
    public function getByUser(User $user): ?YandexSetting
    {
        return YandexSetting::query()
            ->where('user_id', $user->id)
            ->first();
    }

    public function upsertForUser(User $user, string $yandexUrl): YandexSetting
    {
        return YandexSetting::query()->updateOrCreate(
            ['user_id' => $user->id],
            ['yandex_url' => $yandexUrl],
        );
    }
}


