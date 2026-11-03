<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * The latest chats with their recent messages, returned by `GET /v1/latest-chats`.
 */
final class LatestChatsResponse
{
    /**
     * @param list<LatestChat> $chats
     */
    public function __construct(
        public readonly array $chats,
        public readonly int $totalChats,
        public readonly int $numberOfChats,
        public readonly int $numberOfMessages,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $chats = [];
        foreach (self::reqList($data, 'chats') as $row) {
            if (!is_array($row)) {
                throw new ValidationError(
                    message: "Field 'chats' must contain only objects in LatestChatsResponse response",
                );
            }
            /** @var array<string, mixed> $row */
            $chats[] = LatestChat::fromArray($row);
        }

        return new self(
            chats: $chats,
            totalChats: self::reqInt($data, 'totalChats'),
            numberOfChats: self::reqInt($data, 'numberOfChats'),
            numberOfMessages: self::reqInt($data, 'numberOfMessages'),
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function reqInt(array $data, string $key): int
    {
        if (!array_key_exists($key, $data) || !is_int($data[$key])) {
            throw new ValidationError(
                message: "Missing or non-int field '{$key}' in LatestChatsResponse response",
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
                message: "Missing or non-array field '{$key}' in LatestChatsResponse response",
            );
        }
        return array_values($data[$key]);
    }
}
