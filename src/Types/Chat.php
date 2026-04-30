<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

final class Chat
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $name,
        public readonly bool $is_group,
        public readonly ?string $last_message_at,
        public readonly ?int $unread_count,
    ) {
    }

    /**
     * @param array<string, mixed> $raw
     */
    public static function fromArray(array $raw): self
    {
        self::assertString($raw, 'id');
        self::assertStringOrNull($raw, 'name');
        if (!array_key_exists('is_group', $raw) || !is_bool($raw['is_group'])) {
            throw new ValidationError(message: "Missing or non-bool field 'is_group' in Chat response");
        }
        self::assertStringOrNull($raw, 'last_message_at');
        if (array_key_exists('unread_count', $raw) && $raw['unread_count'] !== null && !is_int($raw['unread_count'])) {
            throw new ValidationError(message: "Field 'unread_count' must be int or null in Chat response");
        }

        return new self(
            id: $raw['id'],
            name: $raw['name'] ?? null,
            is_group: $raw['is_group'],
            last_message_at: $raw['last_message_at'] ?? null,
            unread_count: $raw['unread_count'] ?? null,
        );
    }

    /** @param array<string, mixed> $data */
    private static function assertString(array $data, string $key): void
    {
        if (!array_key_exists($key, $data) || !is_string($data[$key])) {
            throw new ValidationError(message: "Missing or non-string field '{$key}' in Chat response");
        }
    }

    /** @param array<string, mixed> $data */
    private static function assertStringOrNull(array $data, string $key): void
    {
        if (array_key_exists($key, $data) && $data[$key] !== null && !is_string($data[$key])) {
            throw new ValidationError(message: "Field '{$key}' must be string or null in Chat response");
        }
    }
}
