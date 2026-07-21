<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * A currently-pinned message, as listed by
 * `GET /v1/messages/pinned/{chatId}`.
 */
final class PinnedMessage
{
    public function __construct(
        public readonly string $key,
        public readonly string $chatId,
        public readonly ?string $text,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        if (!array_key_exists('key', $data) || !is_string($data['key'])) {
            throw new ValidationError(message: "Missing or non-string field 'key' in PinnedMessage response");
        }
        if (!array_key_exists('chatId', $data) || !is_string($data['chatId'])) {
            throw new ValidationError(message: "Missing or non-string field 'chatId' in PinnedMessage response");
        }
        if (array_key_exists('text', $data) && $data['text'] !== null && !is_string($data['text'])) {
            throw new ValidationError(message: "Field 'text' must be string or null in PinnedMessage response");
        }

        return new self(
            key: $data['key'],
            chatId: $data['chatId'],
            text: $data['text'] ?? null,
        );
    }
}
