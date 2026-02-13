<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Exception handler: API JSON responses, validation, custom ApiException.
 */
class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     * For API requests returns a consistent JSON error format.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $e): \Symfony\Component\HttpFoundation\Response
    {
        if ($this->isApiRequest($request)) {
            return $this->apiResponse($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * Determine whether the request is an API request (JSON response expected).
     *
     * @return bool
     */
    protected function isApiRequest(Request $request): bool
    {
        return $request->is('api/*') || $request->expectsJson();
    }

    /**
     * Build a consistent JSON error response for API.
     *
     * @return JsonResponse
     */
    protected function apiResponse(Request $request, Throwable $e): JsonResponse
    {
        if ($e instanceof ApiException) {
            return $this->jsonError(
                $e->getMessage(),
                $e->getStatusCode(),
                [],
            );
        }

        if ($e instanceof ValidationException) {
            $errors = $e->errors();

            return $this->jsonError(
                $e->getMessage(),
                $e->status,
                is_array($errors) ? $errors : $errors->toArray(),
            );
        }

        if (method_exists($e, 'getStatusCode')) {
            $status = $e->getStatusCode();
            $message = $e->getMessage() ?: $this->defaultMessageForStatus($status);
            return $this->jsonError($message, $status, []);
        }

        $message = config('app.debug')
            ? $e->getMessage()
            : 'Внутренняя ошибка сервера. Попробуйте позже.';

        return $this->jsonError($message, Response::HTTP_INTERNAL_SERVER_ERROR, []);
    }

    /**
     * Build a JSON response with a unified error structure.
     *
     * @return JsonResponse
     */
    protected function jsonError(string $message, int $status, array $errors = []): JsonResponse
    {
        $payload = [
            'message' => $message,
        ];

        if ($errors !== []) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }

    /**
     * Default user-facing message for a given HTTP status code.
     *
     * @param  int  $status
     * @return string
     */
    protected function defaultMessageForStatus(int $status): string
    {
        return match ($status) {
            Response::HTTP_UNAUTHORIZED => 'Требуется авторизация.',
            Response::HTTP_FORBIDDEN => 'Доступ запрещён.',
            Response::HTTP_NOT_FOUND => 'Ресурс не найден.',
            Response::HTTP_UNPROCESSABLE_ENTITY => 'Некорректные данные.',
            419 => 'Сессия истекла. Обновите страницу.',
            default => 'Произошла ошибка.',
        };
    }
}
