<?php

declare(strict_types=1);

namespace Masklen\Types;

/**
 * Represents a failed lookup for a single IP within a batch request.
 */
readonly class BatchItemError
{
    public function __construct(
        public string $ip,
        public string $errorCode,
        public string $errorMessage,
    ) {}

    /**
     * Creates a BatchItemError instance from an associative array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            ip: (string) ($data['ip'] ?? ''),
            errorCode: (string) ($data['errorCode'] ?? ''),
            errorMessage: (string) ($data['errorMessage'] ?? ''),
        );
    }
}
