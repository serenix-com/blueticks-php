<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * A WhatsApp engine connected to the workspace, as returned by
 * `GET /v1/engines`.
 */
final class Engine
{
    public const TYPES = ['gateway', 'regular'];

    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly bool $connected,
        public readonly ?string $state,
        public readonly ?string $stream,
        public readonly ?bool $hasSynced,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        if (!array_key_exists('id', $data) || !is_string($data['id'])) {
            throw new ValidationError(message: "Missing or non-string field 'id' in Engine response");
        }
        if (
            !array_key_exists('type', $data)
            || !is_string($data['type'])
            || !in_array($data['type'], self::TYPES, true)
        ) {
            throw new ValidationError(
                message: "Field 'type' must be one of " . implode('/', self::TYPES) . " in Engine response",
            );
        }
        if (!array_key_exists('connected', $data) || !is_bool($data['connected'])) {
            throw new ValidationError(message: "Missing or non-bool field 'connected' in Engine response");
        }
        self::assertStringOrNull($data, 'state');
        self::assertStringOrNull($data, 'stream');
        if (array_key_exists('hasSynced', $data) && $data['hasSynced'] !== null && !is_bool($data['hasSynced'])) {
            throw new ValidationError(message: "Field 'hasSynced' must be bool or null in Engine response");
        }

        return new self(
            id: $data['id'],
            type: $data['type'],
            connected: $data['connected'],
            state: $data['state'] ?? null,
            stream: $data['stream'] ?? null,
            hasSynced: $data['hasSynced'] ?? null,
        );
    }

    /** @param array<string, mixed> $data */
    private static function assertStringOrNull(array $data, string $key): void
    {
        if (array_key_exists($key, $data) && $data[$key] !== null && !is_string($data[$key])) {
            throw new ValidationError(message: "Field '{$key}' must be string or null in Engine response");
        }
    }
}
