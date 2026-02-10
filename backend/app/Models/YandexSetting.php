<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class YandexSetting extends Model
{
    protected $table = 'yandex_settings';

    protected $fillable = [
        'user_id',
        'yandex_url',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}


