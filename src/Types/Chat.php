<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * A WhatsApp chat (conversation) — a 1:1 contact, a group, or a channel
 * — as seen by the connected engine.
 */
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
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        self::assertString($data, 'chatId');
        self::assertStringOrNull($data, 'name');
        if (
            !array_key_exists('chatType', $data)
            || !is_string($data['chatType'])
            || !in_array($data['chatType'], self::CHAT_TYPES, true)
        ) {
            throw new ValidationError(
                message: "Field 'chatType' must be one of " . implode('/', self::CHAT_TYPES) . " in Chat response",
            );
        }
        self::assertBoolOrNull($data, 'pinned');
        self::assertBoolOrNull($data, 'archived');
        self::assertStringOrNull($data, 'lastMessageAt');
        self::assertIntOrNull($data, 'unreadCount');
        if (!array_key_exists('markedUnread', $data) || !is_bool($data['markedUnread'])) {
            throw new ValidationError(message: "Missing or non-bool field 'markedUnread' in Chat response");
        }
        self::assertStringOrNull($data, 'lastMessageText');
        self::assertBoolOrNull($data, 'lastMessageFromMe');

        return new self(
            chatId: $data['chatId'],
            name: $data['name'] ?? null,
            chatType: $data['chatType'],
            pinned: $data['pinned'] ?? null,
            archived: $data['archived'] ?? null,
            lastMessageAt: $data['lastMessageAt'] ?? null,
            unreadCount: $data['unreadCount'] ?? null,
            markedUnread: $data['markedUnread'],
            lastMessageText: $data['lastMessageText'] ?? null,
            lastMessageFromMe: $data['lastMessageFromMe'] ?? null,
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

    /** @param array<string, mixed> $data */
    private static function assertIntOrNull(array $data, string $key): void
    {
        if (array_key_exists($key, $data) && $data[$key] !== null && !is_int($data[$key])) {
            throw new ValidationError(message: "Field '{$key}' must be int or null in Chat response");
        }
    }
}
