<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * Lightweight reference to a chat by id. Returned by endpoints that
 * acknowledge an action against a chat without echoing the full Chat
 * object (e.g. `POST /v1/chats/{chat_id}/open`).
 */
final class ChatRef
{
    public function __construct(
        public readonly string $chat_id,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        if (!array_key_exists('chat_id', $data) || !is_string($data['chat_id'])) {
            throw new ValidationError(message: "Missing or non-string field 'chat_id' in ChatRef response");
        }

        return new self(chat_id: $data['chat_id']);
    }
}
