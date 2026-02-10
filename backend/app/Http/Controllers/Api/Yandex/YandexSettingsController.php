<?php

namespace App\Http\Controllers\Api\Yandex;

use App\Domain\Yandex\Repositories\YandexSettingsRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class YandexSettingsController extends Controller
{
    public function __construct(
        private readonly YandexSettingsRepository $settingsRepository,
    ) {
    }

    public function show(Request $request): JsonResponse
    {
        $setting = $this->settingsRepository->getByUser($request->user());

        return response()->json([
            'yandex_url' => $setting?->yandex_url,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'yandex_url' => ['required', 'url'],
        ]);

        $setting = $this->settingsRepository->upsertForUser(
            $request->user(),
            $data['yandex_url'],
        );

        return response()->json([
            'yandex_url' => $setting->yandex_url,
        ]);
    }
}


