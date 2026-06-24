<?php

declare(strict_types=1);

namespace Masklen\Types;

/**
 * Geographic location data for an IP address.
 */
readonly class Location
{
    public function __construct(
        public ?string $city,
        public ?string $region,
        public ?string $country,
        public ?string $country_code,
        public ?float $latitude,
        public ?float $longitude,
        public ?string $postal_code,
        public ?string $timezone,
    ) {}

    /**
     * Creates a Location instance from an associative array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            city: isset($data['city']) ? (string) $data['city'] : null,
            region: isset($data['region']) ? (string) $data['region'] : null,
            country: isset($data['country']) ? (string) $data['country'] : null,
            country_code: isset($data['country_code']) ? (string) $data['country_code'] : null,
            latitude: isset($data['latitude']) ? (float) $data['latitude'] : null,
            longitude: isset($data['longitude']) ? (float) $data['longitude'] : null,
            postal_code: isset($data['postal_code']) ? (string) $data['postal_code'] : null,
            timezone: isset($data['timezone']) ? (string) $data['timezone'] : null,
        );
    }
}
