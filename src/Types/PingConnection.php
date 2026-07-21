<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * A single connected WhatsApp engine, as listed in {@see Ping}.
 */
final class PingConnection
{
    public const TYPES = ['gateway', 'regular'];

    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly bool $connected,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        if (!array_key_exists('id', $data) || !is_string($data['id'])) {
            throw new ValidationError(message: "Missing or non-string field 'id' in PingConnection response");
        }
        if (
            !array_key_exists('type', $data)
            || !is_string($data['type'])
            || !in_array($data['type'], self::TYPES, true)
        ) {
            throw new ValidationError(
                message: "Field 'type' must be one of " . implode('/', self::TYPES) . " in PingConnection response",
            );
        }
        if (!array_key_exists('connected', $data) || !is_bool($data['connected'])) {
            throw new ValidationError(message: "Missing or non-bool field 'connected' in PingConnection response");
        }

        return new self(
            id: $data['id'],
            type: $data['type'],
            connected: $data['connected'],
        );
    }
}
