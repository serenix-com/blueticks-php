<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

final class WebhookEvent
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly string $createdAt,
        public readonly array $data,
    ) {
    }

    /**
     * @param array<string, mixed> $raw
     */
    public static function fromArray(array $raw): self
    {
        self::assertString($raw, 'id');
        self::assertString($raw, 'type');
        self::assertString($raw, 'created_at');
        if (!array_key_exists('data', $raw) || !is_array($raw['data'])) {
            throw new ValidationError(message: "Missing or non-array field 'data' in WebhookEvent response");
        }

        /** @var array<string, mixed> $data */
        $data = $raw['data'];

        return new self(
            id: $raw['id'],
            type: $raw['type'],
            createdAt: $raw['created_at'],
            data: $data,
        );
    }

    /** @param array<string, mixed> $data */
    private static function assertString(array $data, string $key): void
    {
        if (!array_key_exists($key, $data) || !is_string($data[$key])) {
            throw new ValidationError(message: "Missing or non-string field '{$key}' in WebhookEvent response");
        }
    }
}
