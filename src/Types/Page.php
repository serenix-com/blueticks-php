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
 * The trailing fields are only present on `GET /v1/messages`
 * (which engine answered, the next cursor, store coverage, and the
 * backup/auto-load details). On every other endpoint they are null.
 *
 * @template T of object
 */
final class Page
{
    /**
     * @param list<T>               $data
     * @param ?MessageAnsweredBy    $answeredBy
     * @param ?MessageListCoverage  $coverage
     * @param ?array<string, int>   $sources
     */
    public function __construct(
        public readonly array $data,
        public readonly int $limit,
        public readonly int $skip,
        public readonly int $total,
        public readonly ?MessageAnsweredBy $answeredBy = null,
        public readonly ?string $nextCursor = null,
        public readonly ?bool $hasMore = null,
        public readonly ?MessageListCoverage $coverage = null,
        public readonly ?int $autoLoadedPages = null,
        public readonly ?bool $sinceReached = null,
        public readonly ?array $sources = null,
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

        $answeredBy = null;
        if (isset($raw['answeredBy']) && is_array($raw['answeredBy'])) {
            /** @var array<string, mixed> $ab */
            $ab = $raw['answeredBy'];
            $answeredBy = MessageAnsweredBy::fromArray($ab);
        }
        $coverage = null;
        if (isset($raw['coverage']) && is_array($raw['coverage'])) {
            /** @var array<string, mixed> $cov */
            $cov = $raw['coverage'];
            $coverage = MessageListCoverage::fromArray($cov);
        }
        $sources = null;
        if (isset($raw['sources']) && is_array($raw['sources'])) {
            $sources = [];
            foreach ($raw['sources'] as $name => $count) {
                if (!is_string($name) || !is_int($count)) {
                    throw new ValidationError(
                        message: "Expected 'sources' to map names to integers on paginated response",
                    );
                }
                $sources[$name] = $count;
            }
        }
        $nextCursor = isset($raw['nextCursor']) && is_string($raw['nextCursor']) ? $raw['nextCursor'] : null;
        $hasMore = isset($raw['hasMore']) && is_bool($raw['hasMore']) ? $raw['hasMore'] : null;
        $autoLoadedPages = isset($raw['autoLoadedPages']) && is_int($raw['autoLoadedPages'])
            ? $raw['autoLoadedPages']
            : null;
        $sinceReached = isset($raw['sinceReached']) && is_bool($raw['sinceReached']) ? $raw['sinceReached'] : null;

        return new self(
            $items,
            $raw['limit'],
            $raw['skip'],
            $raw['total'],
            answeredBy: $answeredBy,
            nextCursor: $nextCursor,
            hasMore: $hasMore,
            coverage: $coverage,
            autoLoadedPages: $autoLoadedPages,
            sinceReached: $sinceReached,
            sources: $sources,
        );
    }
}
