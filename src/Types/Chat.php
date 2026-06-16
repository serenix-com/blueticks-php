<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

final class Chat
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $name,
        public readonly bool $isGroup,
        public readonly bool $isNewsletter,
        public readonly ?string $lastMessageAt,
        public readonly ?int $unreadCount,
        public readonly bool $markedUnread,
    ) {
    }

    /**
     * @param array<string, mixed> $raw
     */
    public static function fromArray(array $raw): self
    {
        self::assertString($raw, 'id');
        self::assertStringOrNull($raw, 'name');
        if (!array_key_exists('isGroup', $raw) || !is_bool($raw['isGroup'])) {
            throw new ValidationError(message: "Missing or non-bool field 'isGroup' in Chat response");
        }
        if (!array_key_exists('isNewsletter', $raw) || !is_bool($raw['isNewsletter'])) {
            throw new ValidationError(message: "Missing or non-bool field 'isNewsletter' in Chat response");
        }
        self::assertStringOrNull($raw, 'lastMessageAt');
        if (array_key_exists('unreadCount', $raw) && $raw['unreadCount'] !== null && !is_int($raw['unreadCount'])) {
            throw new ValidationError(message: "Field 'unreadCount' must be int or null in Chat response");
        }
        if (!array_key_exists('markedUnread', $raw) || !is_bool($raw['markedUnread'])) {
            throw new ValidationError(message: "Missing or non-bool field 'markedUnread' in Chat response");
        }

        return new self(
            id: $raw['id'],
            name: $raw['name'] ?? null,
            isGroup: $raw['isGroup'],
            isNewsletter: $raw['isNewsletter'],
            lastMessageAt: $raw['lastMessageAt'] ?? null,
            unreadCount: $raw['unreadCount'] ?? null,
            markedUnread: $raw['markedUnread'],
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
