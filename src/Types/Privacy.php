<?php

declare(strict_types=1);

namespace Masklen\Types;

/**
 * Privacy and threat detection data for an IP address.
 *
 * threat_level is either "low" or "medium".
 */
readonly class Privacy
{
    public function __construct(
        public bool $vpn,
        public bool $proxy,
        public bool $tor,
        public bool $hosting,
        public string $threat_level,
    ) {}

    /**
     * Creates a Privacy instance from an associative array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            vpn: (bool) ($data['vpn'] ?? false),
            proxy: (bool) ($data['proxy'] ?? false),
            tor: (bool) ($data['tor'] ?? false),
            hosting: (bool) ($data['hosting'] ?? false),
            threat_level: (string) ($data['threat_level'] ?? 'low'),
        );
    }
}
