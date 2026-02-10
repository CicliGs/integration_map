<?php

namespace App\Domain\Yandex\Dto;

class YandexReview
{
    public function __construct(
        public readonly string $id,
        public readonly string $date,
        public readonly string $branch,
        public readonly string $phone,
        public readonly int $rating,
        public readonly string $text,
    ) {
    }
}


