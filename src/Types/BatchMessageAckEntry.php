<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * Single row inside `BatchMessageAcksResponse.data`. `key` is the
 * WhatsApp message key the caller asked about; `ack` is the engine
 * delivery code (0=pending, 1=sent, 2=delivered, 3=read, 4=played) or
 * `null` if the engine has not yet observed any state for that key.
 */
final class BatchMessageAckEntry
{
    public function __construct(
        public readonly string $key,
        public readonly ?int $ack,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        self::assertString($data, 'key');
        self::assertIntOrNull($data, 'ack');

        return new self(
            key: $data['key'],
            ack: $data['ack'],
        );
    }

    /** @param array<string, mixed> $data */
    private static function assertString(array $data, string $key): void
    {
        if (!array_key_exists($key, $data) || !is_string($data[$key])) {
            throw new ValidationError(
                message: "Missing or non-string field '{$key}' in BatchMessageAckEntry response",
            );
        }
    }

    /** @param array<string, mixed> $data */
    private static function assertIntOrNull(array $data, string $key): void
    {
        if (!array_key_exists($key, $data)) {
            throw new ValidationError(
                message: "Missing field '{$key}' in BatchMessageAckEntry response",
            );
        }
        if ($data[$key] !== null && !is_int($data[$key])) {
            throw new ValidationError(
                message: "Field '{$key}' must be int or null in BatchMessageAckEntry response",
            );
        }
    }
}
