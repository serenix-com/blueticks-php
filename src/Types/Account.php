<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

final class Account
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly ?string $timezone,
        public readonly string $created_at,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        self::assertString($data, 'id');
        self::assertString($data, 'name');
        self::assertStringOrNull($data, 'timezone');
        self::assertString($data, 'created_at');

        return new self(
            id: $data['id'],
            name: $data['name'],
            timezone: $data['timezone'],
            created_at: $data['created_at'],
        );
    }

    /** @param array<string, mixed> $data */
    private static function assertString(array $data, string $key): void
    {
        if (!array_key_exists($key, $data) || !is_string($data[$key])) {
            throw new ValidationError(message: "Missing or non-string field '{$key}' in Account response");
        }
    }

    /** @param array<string, mixed> $data */
    private static function assertStringOrNull(array $data, string $key): void
    {
        if (!array_key_exists($key, $data)) {
            throw new ValidationError(message: "Missing field '{$key}' in Account response");
        }
        if ($data[$key] !== null && !is_string($data[$key])) {
            throw new ValidationError(message: "Field '{$key}' must be string or null in Account response");
        }
    }
}
