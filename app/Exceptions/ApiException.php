<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;
use Throwable;

/**
 * API exception with HTTP status code; rendered as JSON by Handler.
 */
class ApiException extends Exception
{
    /**
     * @param  string  $message  User-facing error message.
     * @param  int  $code  HTTP status code (100-599).
     * @param  Throwable|null  $previous  Previous exception for chaining.
     */
    public function __construct(
        string $message = 'Server error.',
        int $code = Response::HTTP_INTERNAL_SERVER_ERROR,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * HTTP status code to send in the response.
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        $code = $this->getCode();

        return $code >= 100 && $code < 600
            ? $code
            : Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
