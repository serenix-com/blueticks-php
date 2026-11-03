<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

final class Webhook
{
    public const STATUSES = ['enabled', 'disabled'];

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
        public readonly ?string $signingSecret = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        self::assertString($data, 'id');
        self::assertString($data, 'url');
        self::assertStringList($data, 'events');
        self::assertStringOrNull($data, 'description');
        if (
            !array_key_exists('status', $data)
            || !is_string($data['status'])
            || !in_array($data['status'], self::STATUSES, true)
        ) {
            throw new ValidationError(
                message: "Field 'status' must be one of " . implode('/', self::STATUSES) . " in Webhook response",
            );
        }
        self::assertString($data, 'createdAt');
        self::assertStringOrNull($data, 'signingSecret');

        /** @var list<string> $events */
        $events = array_values($data['events']);

        return new self(
            id: $data['id'],
            url: $data['url'],
            events: $events,
            description: $data['description'] ?? null,
            status: $data['status'],
            createdAt: $data['createdAt'],
            signingSecret: $data['signingSecret'] ?? null,
        );
    }

    /** @param array<string, mixed> $data */
    private static function assertString(array $data, string $key): void
    {
        if (!array_key_exists($key, $data) || !is_string($data[$key])) {
            throw new ValidationError(message: "Missing or non-string field '{$key}' in Webhook response");
        }
    }

    /** @param array<string, mixed> $data */
    private static function assertStringOrNull(array $data, string $key): void
    {
        if (array_key_exists($key, $data) && $data[$key] !== null && !is_string($data[$key])) {
            throw new ValidationError(message: "Field '{$key}' must be string or null in Webhook response");
        }
    }

    /** @param array<string, mixed> $data */
    private static function assertStringList(array $data, string $key): void
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
