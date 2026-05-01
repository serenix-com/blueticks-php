<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * Response payload for `POST /v1/chats/message_acks`. The server
 * returns one row per requested message_key. Row shape is engine-
 * defined and intentionally untyped here ({@see additionalProperties}
 * in the OpenAPI spec) — callers read fields like `key`, `ack`,
 * `read_at` directly off the associative arrays.
 */
final class BatchMessageAcksResponse
{
    /**
     * @param list<array<string, mixed>> $data
     */
    public function __construct(
        public readonly array $data,
    ) {
    }

    /**
     * @param array<string, mixed> $raw
     */
    public static function fromArray(array $raw): self
    {
        if (!array_key_exists('data', $raw) || !is_array($raw['data'])) {
            throw new ValidationError(
                message: "Missing or non-array field 'data' in BatchMessageAcksResponse response",
            );
        }

        $rows = [];
        foreach ($raw['data'] as $row) {
            if (!is_array($row)) {
                throw new ValidationError(
                    message: "Each entry of 'data' must be an object in BatchMessageAcksResponse response",
                );
            }
            /** @var array<string, mixed> $row */
            $rows[] = $row;
        }

        return new self(data: $rows);
    }
}
