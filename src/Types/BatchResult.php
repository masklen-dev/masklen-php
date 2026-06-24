<?php

declare(strict_types=1);

namespace Masklen\Types;

/**
 * The result of a batch IP lookup.
 *
 * Each entry in $results is either a LookupResult (success) or a
 * BatchItemError (failure for that specific IP).
 */
class BatchResult
{
    /**
     * @param array<int, LookupResult|BatchItemError> $results
     */
    public function __construct(
        public readonly array $results,
    ) {}

    /**
     * Creates a BatchResult instance from an associative array.
     *
     * Items that contain an "errorCode" key are treated as errors.
     * All other items are treated as successful lookup results.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $results = [];

        $items = $data['results'] ?? [];
        if (is_array($items)) {
            foreach ($items as $item) {
                if (!is_array($item)) {
                    continue;
                }

                if (isset($item['errorCode'])) {
                    $results[] = BatchItemError::fromArray($item);
                } else {
                    $results[] = LookupResult::fromArray($item);
                }
            }
        }

        return new self(results: $results);
    }
}
