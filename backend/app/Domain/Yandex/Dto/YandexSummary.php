<?php

namespace App\Domain\Yandex\Dto;

class YandexSummary
{
    public function __construct(
        public readonly float $rating,
        public readonly int $reviewsCount,
    ) {
    }
}


