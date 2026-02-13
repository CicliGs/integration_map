<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Single-row settings (e.g. Yandex reviews URL).
 *
 * @property string|null $yandex_reviews_url
 */
class Setting extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'yandex_reviews_url',
    ];
}
