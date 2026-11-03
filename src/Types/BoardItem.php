<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * A board item (task) with its resolved status and priority options.
 */
final class BoardItem
{
    /**
     * @param array<string, mixed>|null $cells
     */
    public function __construct(
        public readonly string $item_id,
        public readonly string $text,
        public readonly ?OptionRef $status,
        public readonly ?OptionRef $priority,
        public readonly ?string $due_date,
        public readonly ?array $cells,
        public readonly ?ItemSource $source,
        public readonly ?float $confidence,
        public readonly ?int $last_seen_at,
        public readonly string $web_url,
        public readonly string $created_at,
        public readonly string $updated_at,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $statusData = self::optMap($data, 'status');
        $priorityData = self::optMap($data, 'priority');
        $sourceData = self::optMap($data, 'source');

        return new self(
            item_id: self::reqString($data, 'item_id'),
            text: self::reqString($data, 'text'),
            status: $statusData === null ? null : OptionRef::fromArray($statusData),
            priority: $priorityData === null ? null : OptionRef::fromArray($priorityData),
            due_date: self::optString($data, 'due_date'),
            cells: self::optMap($data, 'cells'),
            source: $sourceData === null ? null : ItemSource::fromArray($sourceData),
            confidence: self::optFloat($data, 'confidence'),
            last_seen_at: self::optInt($data, 'last_seen_at'),
            web_url: self::reqString($data, 'web_url'),
            created_at: self::reqString($data, 'created_at'),
            updated_at: self::reqString($data, 'updated_at'),
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function optFloat(array $data, string $key): ?float
    {
        if (!array_key_exists($key, $data) || $data[$key] === null) {
            return null;
        }
        if (!is_int($data[$key]) && !is_float($data[$key])) {
            throw new ValidationError(
                message: "Field '{$key}' must be number or null in BoardItem response",
            );
        }
        return (float) $data[$key];
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
                message: "Field '{$key}' must be int or null in BoardItem response",
            );
        }
        return $data[$key];
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>|null
     */
    private static function optMap(array $data, string $key): ?array
    {
        if (!array_key_exists($key, $data) || $data[$key] === null) {
            return null;
        }
        if (!is_array($data[$key])) {
            throw new ValidationError(
                message: "Field '{$key}' must be an object or null in BoardItem response",
            );
        }
        /** @var array<string, mixed> $value */
        $value = $data[$key];
        return $value;
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
                message: "Field '{$key}' must be string or null in BoardItem response",
            );
        }
        return $data[$key];
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function reqString(array $data, string $key): string
    {
        if (!array_key_exists($key, $data) || !is_string($data[$key])) {
            throw new ValidationError(
                message: "Missing or non-string field '{$key}' in BoardItem response",
            );
        }
        return $data[$key];
    }
}
