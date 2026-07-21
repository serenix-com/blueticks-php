<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * One entry in the `POST /v1/messages/acks` batch response.
 *
 * `ack` semantics: -1=error, 0=pending, 1=server, 2=device, 3=read,
 * 4=played; null when the message has no determined ack or was not found
 * (see `found`).
 */
final class BatchMessageAck
{
    public function __construct(
        public readonly string $key,
        public readonly ?int $ack,
        public readonly bool $found,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        if (!array_key_exists('key', $data) || !is_string($data['key'])) {
            throw new ValidationError(message: "Missing or non-string field 'key' in BatchMessageAck response");
        }
        if (array_key_exists('ack', $data) && $data['ack'] !== null && !is_int($data['ack'])) {
            throw new ValidationError(message: "Field 'ack' must be int or null in BatchMessageAck response");
        }
        if (!array_key_exists('found', $data) || !is_bool($data['found'])) {
            throw new ValidationError(message: "Missing or non-bool field 'found' in BatchMessageAck response");
        }

        return new self(
            key: $data['key'],
            ack: $data['ack'] ?? null,
            found: $data['found'],
        );
    }
}
