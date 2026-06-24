<?php

declare(strict_types=1);

namespace Masklen\Types;

/**
 * Locale and regional data for an IP address.
 */
readonly class Locale
{
    /**
     * @param string[] $languages
     */
    public function __construct(
        public ?string $currency,
        public ?string $currency_symbol,
        public ?string $calling_code,
        public array $languages,
        public ?string $flag,
    ) {}

    /**
     * Creates a Locale instance from an associative array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $languages = [];
        if (isset($data['languages']) && is_array($data['languages'])) {
            foreach ($data['languages'] as $lang) {
                $languages[] = (string) $lang;
            }
        }

        return new self(
            currency: isset($data['currency']) ? (string) $data['currency'] : null,
            currency_symbol: isset($data['currency_symbol']) ? (string) $data['currency_symbol'] : null,
            calling_code: isset($data['calling_code']) ? (string) $data['calling_code'] : null,
            languages: $languages,
            flag: isset($data['flag']) ? (string) $data['flag'] : null,
        );
    }
}
