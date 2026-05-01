<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * Response payload for
 * `POST /v1/chats/{chat_id}/messages/load_older`. Reports how many
 * historical messages the engine ingested from the phone and whether
 * more pages remain.
 */
final class LoadOlderMessagesResponse
{
    public function __construct(
        public readonly ?int $total_messages,
        public readonly ?int $added,
        public readonly bool $can_load_more,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        self::assertIntOrNull($data, 'total_messages');
        self::assertIntOrNull($data, 'added');
        if (!array_key_exists('can_load_more', $data) || !is_bool($data['can_load_more'])) {
            throw new ValidationError(
                message: "Missing or non-bool field 'can_load_more' in LoadOlderMessagesResponse response",
            );
        }

        /** @var ?int $totalMessages */
        $totalMessages = $data['total_messages'];
        /** @var ?int $added */
        $added = $data['added'];

        return new self(
            total_messages: $totalMessages,
            added: $added,
            can_load_more: $data['can_load_more'],
        );
    }

    /** @param array<string, mixed> $data */
    private static function assertIntOrNull(array $data, string $key): void
    {
        if (!array_key_exists($key, $data)) {
            throw new ValidationError(message: "Missing field '{$key}' in LoadOlderMessagesResponse response");
        }
        if ($data[$key] !== null && !is_int($data[$key])) {
            throw new ValidationError(
                message: "Field '{$key}' must be int or null in LoadOlderMessagesResponse response",
            );
        }
    }
}
