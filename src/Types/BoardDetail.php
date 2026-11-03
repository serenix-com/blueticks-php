<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * A board with its columns, as returned by `GET /v1/boards/{id}` and `POST /v1/boards`.
 */
final class BoardDetail
{
    /**
     * @param list<BoardColumn> $columns
     */
    public function __construct(
        public readonly string $board_id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?string $domain,
        public readonly bool $agent_managed,
        public readonly ?int $memory_retention_days,
        public readonly ?int $item_count,
        public readonly string $web_url,
        public readonly string $created_at,
        public readonly string $updated_at,
        public readonly array $columns,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $columns = [];
        foreach (self::reqList($data, 'columns') as $row) {
            if (!is_array($row)) {
                throw new ValidationError(
                    message: "Field 'columns' must contain only objects in BoardDetail response",
                );
            }
            /** @var array<string, mixed> $row */
            $columns[] = BoardColumn::fromArray($row);
        }

        return new self(
            board_id: self::reqString($data, 'board_id'),
            name: self::reqString($data, 'name'),
            description: self::optString($data, 'description'),
            domain: self::optString($data, 'domain'),
            agent_managed: self::reqBool($data, 'agent_managed'),
            memory_retention_days: self::optInt($data, 'memory_retention_days'),
            item_count: self::optInt($data, 'item_count'),
            web_url: self::reqString($data, 'web_url'),
            created_at: self::reqString($data, 'created_at'),
            updated_at: self::reqString($data, 'updated_at'),
            columns: $columns,
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function optInt(array $data, string $key): ?int
    {
        if (!array_key_exists($key, $data) || $data[$key] === null) {
            return null;
        }
        if (!is_int($data[$key])) {
            throw new ValidationError(
                message: "Field '{$key}' must be int or null in BoardDetail response",
            );
        }
        return $data[$key];
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function optString(array $data, string $key): ?string
    {
        if (!array_key_exists($key, $data) || $data[$key] === null) {
            return null;
        }
        if (!is_string($data[$key])) {
            throw new ValidationError(
                message: "Field '{$key}' must be string or null in BoardDetail response",
            );
        }
        return $data[$key];
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function reqBool(array $data, string $key): bool
    {
        if (!array_key_exists($key, $data) || !is_bool($data[$key])) {
            throw new ValidationError(
                message: "Missing or non-bool field '{$key}' in BoardDetail response",
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
                message: "Missing or non-array field '{$key}' in BoardDetail response",
            );
        }
        return array_values($data[$key]);
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function reqString(array $data, string $key): string
    {
        if (!array_key_exists($key, $data) || !is_string($data[$key])) {
            throw new ValidationError(
                message: "Missing or non-string field '{$key}' in BoardDetail response",
            );
        }
        return $data[$key];
    }
}
