<?php

declare(strict_types=1);

namespace Masklen\Types;

/**
 * The full result of an IP address lookup.
 *
 * Fields that were not requested via the fields parameter will be null.
 */
readonly class LookupResult
{
    public function __construct(
        public string $ip,
        public ?Location $location,
        public ?Network $network,
        public ?Privacy $privacy,
        public ?Locale $locale,
    ) {}

    /**
     * Creates a LookupResult instance from an associative array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            ip: (string) ($data['ip'] ?? ''),
            location: isset($data['location']) && is_array($data['location'])
                ? Location::fromArray($data['location'])
                : null,
            network: isset($data['network']) && is_array($data['network'])
                ? Network::fromArray($data['network'])
                : null,
            privacy: isset($data['privacy']) && is_array($data['privacy'])
                ? Privacy::fromArray($data['privacy'])
                : null,
            locale: isset($data['locale']) && is_array($data['locale'])
                ? Locale::fromArray($data['locale'])
                : null,
        );
    }
}
