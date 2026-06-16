<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * Response payload for
 * `POST /v1/messages/load_older/{chatId}`. Reports how many
 * historical messages the engine ingested from the phone and whether
 * more pages remain.
 */
final class LoadOlderMessagesResponse
{
    public function __construct(
        public readonly ?int $totalMessages,
        public readonly ?int $added,
        public readonly bool $canLoadMore,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        self::assertIntOrNull($data, 'totalMessages');
        self::assertIntOrNull($data, 'added');
        if (!array_key_exists('canLoadMore', $data) || !is_bool($data['canLoadMore'])) {
            throw new ValidationError(
                message: "Missing or non-bool field 'canLoadMore' in LoadOlderMessagesResponse response",
            );
        }

        /** @var ?int $totalMessages */
        $totalMessages = $data['totalMessages'];
        /** @var ?int $added */
        $added = $data['added'];

        return new self(
            totalMessages: $totalMessages,
            added: $added,
            canLoadMore: $data['canLoadMore'],
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
