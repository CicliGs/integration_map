<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Update Yandex settings validation (yandex_reviews_url required, url, Yandex Maps only).
 */
class UpdateYandexSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string|\Closure>>
     */
    public function rules(): array
    {
        return [
            'yandex_reviews_url' => [
                'required',
                'string',
                'url',
                'max:2048',
                function (string $attribute, string $value, \Closure $fail): void {
                    $host = parse_url($value, PHP_URL_HOST) ?? '';
                    $path = parse_url($value, PHP_URL_PATH) ?? '';
                    $isYandex = (bool) preg_match('/^(www\.)?(yandex\.(ru|by|kz|com|com\.tr)|yandex\.com)$/i', $host);
                    $hasMaps = str_contains(strtolower($path), 'maps');
                    if (! $isYandex || ! $hasMaps) {
                        $fail('Ссылка должна вести на страницу организации в Яндекс.Картах.');
                    }
                },
            ],
        ];
    }
}
