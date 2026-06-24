<?php

declare(strict_types=1);

namespace Masklen\Exceptions;

use RuntimeException;

/**
 * Thrown when the masklen.dev API returns an error response.
 */
class MasklenException extends RuntimeException
{
    public function __construct(
        private readonly int $statusCode,
        private readonly string $errorCode,
        private readonly string $errorMessage,
    ) {
        parent::__construct(
            sprintf('[%s] %s (HTTP %d)', $errorCode, $errorMessage, $statusCode)
        );
    }

    /**
     * Returns the HTTP status code from the API response.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Returns the machine-readable error code from the API response.
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * Returns the human-readable error message from the API response.
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }
}
