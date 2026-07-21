<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

final class Audience
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly int $contactCount,
        public readonly string $createdAt,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        self::assertString($data, 'id');
        self::assertString($data, 'name');
        if (!array_key_exists('contactCount', $data) || !is_int($data['contactCount'])) {
            throw new ValidationError(message: "Missing or non-int field 'contactCount' in Audience response");
        }
        self::assertString($data, 'createdAt');

        return new self(
            id: $data['id'],
            name: $data['name'],
            contactCount: $data['contactCount'],
            createdAt: $data['createdAt'],
        );
    }

    /** @param array<string, mixed> $data */
    private static function assertString(array $data, string $key): void
    {
        if (!array_key_exists($key, $data) || !is_string($data[$key])) {
            throw new ValidationError(message: "Missing or non-string field '{$key}' in Audience response");
        }
    }
}
