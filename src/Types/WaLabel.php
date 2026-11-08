<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * A native WhatsApp Business label. `name` and `color` may be absent on
 * some responses; they are then null.
 */
final class WaLabel
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $name = null,
        public readonly ?string $color = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        self::assertString($data, 'id');
        self::assertStringOrNull($data, 'name');
        self::assertStringOrNull($data, 'color');

        return new self(
            id: $data['id'],
            name: isset($data['name']) && is_string($data['name']) ? $data['name'] : null,
            color: isset($data['color']) && is_string($data['color']) ? $data['color'] : null,
        );
    }

    /** @param array<string, mixed> $data */
    private static function assertString(array $data, string $key): void
    {
        if (!array_key_exists($key, $data) || !is_string($data[$key])) {
            throw new ValidationError(message: "Missing or non-string field '{$key}' in WaLabel response");
        }
    }

    /** @param array<string, mixed> $data */
    private static function assertStringOrNull(array $data, string $key): void
    {
        if (array_key_exists($key, $data) && $data[$key] !== null && !is_string($data[$key])) {
            throw new ValidationError(message: "Field '{$key}' must be string or null in WaLabel response");
        }
    }
}
