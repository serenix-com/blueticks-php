<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * A board column and its selectable options.
 */
final class BoardColumn
{
    /**
     * @param list<OptionRef> $options
     */
    public function __construct(
        public readonly string $column_id,
        public readonly string $title,
        public readonly string $type,
        public readonly array $options,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $options = [];
        foreach (self::reqList($data, 'options') as $row) {
            if (!is_array($row)) {
                throw new ValidationError(
                    message: "Field 'options' must contain only objects in BoardColumn response",
                );
            }
            /** @var array<string, mixed> $row */
            $options[] = OptionRef::fromArray($row);
        }

        return new self(
            column_id: self::reqString($data, 'column_id'),
            title: self::reqString($data, 'title'),
            type: self::reqString($data, 'type'),
            options: $options,
        );
    }

    /**
     * @param array<string, mixed> $data
     * @return list<mixed>
     */
    private static function reqList(array $data, string $key): array
    {
        if (!array_key_exists($key, $data) || !is_array($data[$key])) {
            throw new ValidationError(
                message: "Missing or non-array field '{$key}' in BoardColumn response",
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
                message: "Missing or non-string field '{$key}' in BoardColumn response",
            );
        }
        return $data[$key];
    }
}
