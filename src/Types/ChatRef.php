<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * Lightweight reference to a chat by id. Returned by endpoints that
 * acknowledge an action against a chat without echoing the full Chat
 * object (e.g. `POST /v1/chats/{chatId}/open`).
 */
final class ChatRef
{
    public function __construct(
        public readonly string $chatId,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        if (!array_key_exists('chatId', $data) || !is_string($data['chatId'])) {
            throw new ValidationError(message: "Missing or non-string field 'chatId' in ChatRef response");
        }

        return new self(chatId: $data['chatId']);
    }
}
