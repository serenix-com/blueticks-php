<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

final class Chat
{
    public const CHAT_TYPES = ['contact', 'group', 'newsletter'];

    public function __construct(
        public readonly string $chatId,
        public readonly ?string $name,
        public readonly string $chatType,
        public readonly ?bool $pinned,
        public readonly ?bool $archived,
        public readonly ?string $lastMessageAt,
        public readonly ?int $unreadCount,
        public readonly bool $markedUnread,
        public readonly ?string $lastMessageText,
        public readonly ?bool $lastMessageFromMe,
    ) {
    }

    /**
     * @param array<string, mixed> $raw
     */
    public static function fromArray(array $raw): self
    {
        self::assertString($raw, 'chatId');
        self::assertStringOrNull($raw, 'name');
        if (
            !array_key_exists('chatType', $raw)
            || !is_string($raw['chatType'])
            || !in_array($raw['chatType'], self::CHAT_TYPES, true)
        ) {
            throw new ValidationError(message: "Missing or invalid enum field 'chatType' in Chat response");
        }
        self::assertBoolOrNull($raw, 'pinned');
        self::assertBoolOrNull($raw, 'archived');
        self::assertStringOrNull($raw, 'lastMessageAt');
        if (array_key_exists('unreadCount', $raw) && $raw['unreadCount'] !== null && !is_int($raw['unreadCount'])) {
            throw new ValidationError(message: "Field 'unreadCount' must be int or null in Chat response");
        }
        if (!array_key_exists('markedUnread', $raw) || !is_bool($raw['markedUnread'])) {
            throw new ValidationError(message: "Missing or non-bool field 'markedUnread' in Chat response");
        }
        self::assertStringOrNull($raw, 'lastMessageText');
        self::assertBoolOrNull($raw, 'lastMessageFromMe');

        return new self(
            chatId: $raw['chatId'],
            name: $raw['name'] ?? null,
            chatType: $raw['chatType'],
            pinned: $raw['pinned'] ?? null,
            archived: $raw['archived'] ?? null,
            lastMessageAt: $raw['lastMessageAt'] ?? null,
            unreadCount: $raw['unreadCount'] ?? null,
            markedUnread: $raw['markedUnread'],
            lastMessageText: $raw['lastMessageText'] ?? null,
            lastMessageFromMe: $raw['lastMessageFromMe'] ?? null,
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

    /** @param array<string, mixed> $data */
    private static function assertBoolOrNull(array $data, string $key): void
    {
        if (array_key_exists($key, $data) && $data[$key] !== null && !is_bool($data[$key])) {
            throw new ValidationError(message: "Field '{$key}' must be bool or null in Chat response");
        }
    }
}
