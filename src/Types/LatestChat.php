<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * A chat with its most recent messages (newest first), as returned by `GET /v1/latest-
 * chats`.
 */
final class LatestChat
{
    public const CHAT_TYPES = ['contact', 'group', 'newsletter'];

    /**
     * @param list<PublicMessage> $messages
     */
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
        public readonly array $messages,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $messages = [];
        foreach (self::reqList($data, 'messages') as $row) {
            if (!is_array($row)) {
                throw new ValidationError(
                    message: "Field 'messages' must contain only objects in LatestChat response",
                );
            }
            /** @var array<string, mixed> $row */
            $messages[] = PublicMessage::fromArray($row);
        }

        return new self(
            chatId: self::reqString($data, 'chatId'),
            name: self::optString($data, 'name'),
            chatType: self::reqEnum($data, 'chatType', self::CHAT_TYPES),
            pinned: self::optBool($data, 'pinned'),
            archived: self::optBool($data, 'archived'),
            lastMessageAt: self::optString($data, 'lastMessageAt'),
            unreadCount: self::optInt($data, 'unreadCount'),
            markedUnread: self::reqBool($data, 'markedUnread'),
            lastMessageText: self::optString($data, 'lastMessageText'),
            lastMessageFromMe: self::optBool($data, 'lastMessageFromMe'),
            messages: $messages,
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function optBool(array $data, string $key): ?bool
    {
        if (!array_key_exists($key, $data) || $data[$key] === null) {
            return null;
        }
        if (!is_bool($data[$key])) {
            throw new ValidationError(
                message: "Field '{$key}' must be bool or null in LatestChat response",
            );
        }
        return $data[$key];
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
                message: "Field '{$key}' must be int or null in LatestChat response",
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
                message: "Field '{$key}' must be string or null in LatestChat response",
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
                message: "Missing or non-bool field '{$key}' in LatestChat response",
            );
        }
        return $data[$key];
    }

    /**
     * @param array<string, mixed> $data
     * @param list<string> $allowed
     */
    private static function reqEnum(array $data, string $key, array $allowed): string
    {
        if (!array_key_exists($key, $data) || !is_string($data[$key]) || !in_array($data[$key], $allowed, true)) {
            throw new ValidationError(
                message: "Field '{$key}' must be one of " . implode('/', $allowed) . " in LatestChat response",
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
                message: "Missing or non-array field '{$key}' in LatestChat response",
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
                message: "Missing or non-string field '{$key}' in LatestChat response",
            );
        }
        return $data[$key];
    }
}
