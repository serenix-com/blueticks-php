<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * Cursor-paginated list envelope returned by every v1 list endpoint.
 *
 * Iterate `data` for the current page; pass `next_cursor` back as the
 * `cursor` argument of the next `list()` call to continue. When
 * `has_more` is false, `next_cursor` is null and iteration is complete.
 *
 * @template T of object
 */
final class Page
{
    /**
     * @param list<T>      $data
     */
    public function __construct(
        public readonly array $data,
        public readonly bool $has_more,
        public readonly ?string $next_cursor,
    ) {
    }

    /**
     * @template  U of object
     * @param    array<string, mixed>      $raw
     * @param    callable(array<string, mixed>): U $mapItem
     * @return   self<U>
     */
    public static function fromArray(array $raw, callable $mapItem): self
    {
        if (!is_array($raw['data'] ?? null)) {
            throw new ValidationError(message: "Expected 'data' to be an array on paginated response");
        }
        if (!is_bool($raw['has_more'] ?? null)) {
            throw new ValidationError(message: "Expected 'has_more' to be a boolean on paginated response");
        }
        $nextCursor = $raw['next_cursor'] ?? null;
        if ($nextCursor !== null && !is_string($nextCursor)) {
            throw new ValidationError(message: "Expected 'next_cursor' to be a string or null on paginated response");
        }

        $items = [];
        foreach ($raw['data'] as $row) {
            if (!is_array($row)) {
                throw new ValidationError(message: "Expected each element of 'data' to be an object");
            }
            /** @var array<string, mixed> $row */
            $items[] = $mapItem($row);
        }

        return new self($items, $raw['has_more'], $nextCursor);
    }
}
