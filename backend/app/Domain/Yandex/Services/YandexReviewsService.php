<?php

namespace App\Domain\Yandex\Services;

use App\Domain\Yandex\Dto\YandexReview;
use App\Domain\Yandex\Dto\YandexSummary;
use App\Models\YandexSetting;

class YandexReviewsService
{
    public function __construct()
    {
        // В будущем сюда можно внедрить HTTP‑клиент для обращения к API Яндекс
    }

    /**
     * Возвращает сводную информацию и список отзывов по настройке.
     * Пока реализовано как мок-данные для фронтенд-верстки.
     *
     * @return array{summary: YandexSummary, reviews: YandexReview[]}
     */
    public function getReviewsForSetting(YandexSetting $setting): array
    {
        // TODO: заменить на реальный вызов API Яндекс

        $reviews = [
            new YandexReview(
                id: '1',
                date: '12.09.2022 14:22',
                branch: 'Филиал 1',
                phone: '+7 900 540 40 40',
                rating: 5,
                text: 'Текст отзыва №1...'
            ),
            new YandexReview(
                id: '2',
                date: '12.09.2022 14:22',
                branch: 'Филиал 1',
                phone: '+7 900 540 40 40',
                rating: 4,
                text: 'Текст отзыва №2...'
            ),
        ];

        $summary = new YandexSummary(
            rating: 4.7,
            reviewsCount: 1145,
        );

        return [
            'summary' => $summary,
            'reviews' => $reviews,
        ];
    }
}


