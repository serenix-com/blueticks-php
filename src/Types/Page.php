<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * Offset-paginated list envelope returned by every v1 list endpoint.
 *
 * Iterate `data` for the current page. Advance by adding `limit` to
 * `skip` on the next `list()` call; iteration is complete once
 * `skip + count(data) >= total`.
 *
 * @template T of object
 */
final class Page
{
    /**
     * @param list<T> $data
     */
    public function __construct(
        public readonly array $data,
        public readonly int $limit,
        public readonly int $skip,
        public readonly int $total,
    ) {
    }

    /**
     * @template  U of object
     * @param    array<string, mixed>              $raw
     * @param    callable(array<string, mixed>): U $mapItem
     * @return   self<U>
     */
    public static function fromArray(array $raw, callable $mapItem): self
    {
        if (!is_array($raw['data'] ?? null)) {
            throw new ValidationError(message: "Expected 'data' to be an array on paginated response");
        }
        if (!is_int($raw['limit'] ?? null)) {
            throw new ValidationError(message: "Expected 'limit' to be an integer on paginated response");
        }
        if (!is_int($raw['skip'] ?? null)) {
            throw new ValidationError(message: "Expected 'skip' to be an integer on paginated response");
        }
        if (!is_int($raw['total'] ?? null)) {
            throw new ValidationError(message: "Expected 'total' to be an integer on paginated response");
        }

        $items = [];
        foreach ($raw['data'] as $row) {
            if (!is_array($row)) {
                throw new ValidationError(message: "Expected each element of 'data' to be an object");
            }
            /** @var array<string, mixed> $row */
            $items[] = $mapItem($row);
        }

        return new self($items, $raw['limit'], $raw['skip'], $raw['total']);
    }
}
