<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * Result of a bulk upsert: the created and updated items, the unchanged count, and the
 * board.
 */
final class BulkItemsResponse
{
    /**
     * @param list<BoardItem> $created
     * @param list<BoardItem> $updated
     */
    public function __construct(
        public readonly array $created,
        public readonly array $updated,
        public readonly int $unchanged,
        public readonly BoardRef $board,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $created = [];
        foreach (self::reqList($data, 'created') as $row) {
            if (!is_array($row)) {
                throw new ValidationError(
                    message: "Field 'created' must contain only objects in BulkItemsResponse response",
                );
            }
            /** @var array<string, mixed> $row */
            $created[] = BoardItem::fromArray($row);
        }
        $updated = [];
        foreach (self::reqList($data, 'updated') as $row) {
            if (!is_array($row)) {
                throw new ValidationError(
                    message: "Field 'updated' must contain only objects in BulkItemsResponse response",
                );
            }
            /** @var array<string, mixed> $row */
            $updated[] = BoardItem::fromArray($row);
        }
        $boardData = self::reqMap($data, 'board');

        return new self(
            created: $created,
            updated: $updated,
            unchanged: self::reqInt($data, 'unchanged'),
            board: BoardRef::fromArray($boardData),
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function reqInt(array $data, string $key): int
    {
        if (!array_key_exists($key, $data) || !is_int($data[$key])) {
            throw new ValidationError(
                message: "Missing or non-int field '{$key}' in BulkItemsResponse response",
            );
        }
        return $data[$key];
    }

    /**
     * @param array<string, mixed> $data
     * @return list<mixed>
     */
    private static function reqList(array $data, string $key): array
    {
        if (!array_key_exists($key, $data) || !is_array($data[$key])) {
            throw new ValidationError(
                message: "Missing or non-array field '{$key}' in BulkItemsResponse response",
            );
        }
        return array_values($data[$key]);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private static function reqMap(array $data, string $key): array
    {
        if (!array_key_exists($key, $data) || !is_array($data[$key])) {
            throw new ValidationError(
                message: "Missing or non-object field '{$key}' in BulkItemsResponse response",
            );
        }
        /** @var array<string, mixed> $value */
        $value = $data[$key];
        return $value;
    }
}
