<?php

declare(strict_types=1);

namespace Masklen\Types;

/**
 * Network and ISP data for an IP address.
 */
readonly class Network
{
    public function __construct(
        public ?string $asn,
        public ?string $isp,
        public ?string $organization,
        public ?string $domain,
    ) {}

    /**
     * Creates a Network instance from an associative array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            asn: isset($data['asn']) ? (string) $data['asn'] : null,
            isp: isset($data['isp']) ? (string) $data['isp'] : null,
            organization: isset($data['organization']) ? (string) $data['organization'] : null,
            domain: isset($data['domain']) ? (string) $data['domain'] : null,
        );
    }
}
