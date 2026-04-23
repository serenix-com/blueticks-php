<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

class Webhook
{
    /**
     * @param list<string> $events
     */
    public function __construct(
        public readonly string $id,
        public readonly string $url,
        public readonly array $events,
        public readonly ?string $description,
        public readonly string $status,
        public readonly string $createdAt,
    ) {
    }

    /**
     * @param array<string, mixed> $raw
     */
    public static function fromArray(array $raw): self
    {
        self::assertString($raw, 'id');
        self::assertString($raw, 'url');
        self::assertStringList($raw, 'events');
        self::assertStringOrNull($raw, 'description');
        self::assertString($raw, 'status');
        self::assertString($raw, 'created_at');

        /** @var list<string> $events */
        $events = array_values($raw['events']);

        return new self(
            id: $raw['id'],
            url: $raw['url'],
            events: $events,
            description: $raw['description'],
            status: $raw['status'],
            createdAt: $raw['created_at'],
        );
    }

    /** @param array<string, mixed> $data */
    protected static function assertString(array $data, string $key): void
    {
        if (!array_key_exists($key, $data) || !is_string($data[$key])) {
            throw new ValidationError(message: "Missing or non-string field '{$key}' in Webhook response");
        }
    }

    /** @param array<string, mixed> $data */
    protected static function assertStringOrNull(array $data, string $key): void
    {
        if (!array_key_exists($key, $data)) {
            throw new ValidationError(message: "Missing field '{$key}' in Webhook response");
        }
        if ($data[$key] !== null && !is_string($data[$key])) {
            throw new ValidationError(message: "Field '{$key}' must be string or null in Webhook response");
        }
    }

    /** @param array<string, mixed> $data */
    protected static function assertStringList(array $data, string $key): void
    {
        if (!array_key_exists($key, $data) || !is_array($data[$key])) {
            throw new ValidationError(message: "Missing or non-array field '{$key}' in Webhook response");
        }
        foreach ($data[$key] as $item) {
            if (!is_string($item)) {
                throw new ValidationError(message: "Field '{$key}' must contain only strings in Webhook response");
            }
        }
    }
}
