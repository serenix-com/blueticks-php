<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * A WhatsApp message key — the engine's addressing tuple for a message.
 * `_serialized` is the compact string form accepted by the message
 * endpoints as `{waMessageKey}`.
 */
final class WaMessageKey
{
    public function __construct(
        public readonly bool $fromMe,
        public readonly ?string $remote,
        public readonly ?string $id,
        public readonly ?string $_serialized,
        public readonly ?string $participant,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        if (!array_key_exists('fromMe', $data) || !is_bool($data['fromMe'])) {
            throw new ValidationError(message: "Missing or non-bool field 'fromMe' in WaMessageKey response");
        }
        self::assertStringOrNull($data, 'remote');
        self::assertStringOrNull($data, 'id');
        self::assertStringOrNull($data, '_serialized');
        self::assertStringOrNull($data, 'participant');

        return new self(
            fromMe: $data['fromMe'],
            remote: $data['remote'] ?? null,
            id: $data['id'] ?? null,
            _serialized: $data['_serialized'] ?? null,
            participant: $data['participant'] ?? null,
        );
    }

    /** @param array<string, mixed> $data */
    private static function assertStringOrNull(array $data, string $key): void
    {
        if (array_key_exists($key, $data) && $data[$key] !== null && !is_string($data[$key])) {
            throw new ValidationError(message: "Field '{$key}' must be string or null in WaMessageKey response");
        }
    }
}
