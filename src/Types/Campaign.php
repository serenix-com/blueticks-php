<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

final class Campaign
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $audience_id,
        public readonly string $status,
        public readonly int $total_count,
        public readonly int $sent_count,
        public readonly int $delivered_count,
        public readonly int $read_count,
        public readonly int $failed_count,
        public readonly ?string $from,
        public readonly string $created_at,
        public readonly ?string $started_at,
        public readonly ?string $completed_at,
        public readonly ?string $aborted_at,
    ) {
    }

    /**
     * @param array<string, mixed> $raw
     */
    public static function fromArray(array $raw): self
    {
        self::assertString($raw, 'id');
        self::assertString($raw, 'name');
        self::assertString($raw, 'audience_id');
        self::assertString($raw, 'status');
        self::assertInt($raw, 'total_count');
        self::assertInt($raw, 'sent_count');
        self::assertInt($raw, 'delivered_count');
        self::assertInt($raw, 'read_count');
        self::assertInt($raw, 'failed_count');
        self::assertStringOrNull($raw, 'from');
        self::assertString($raw, 'created_at');
        self::assertStringOrNull($raw, 'started_at');
        self::assertStringOrNull($raw, 'completed_at');
        self::assertStringOrNull($raw, 'aborted_at');

        return new self(
            id: $raw['id'],
            name: $raw['name'],
            audience_id: $raw['audience_id'],
            status: $raw['status'],
            total_count: $raw['total_count'],
            sent_count: $raw['sent_count'],
            delivered_count: $raw['delivered_count'],
            read_count: $raw['read_count'],
            failed_count: $raw['failed_count'],
            from: $raw['from'],
            created_at: $raw['created_at'],
            started_at: $raw['started_at'],
            completed_at: $raw['completed_at'],
            aborted_at: $raw['aborted_at'],
        );
    }

    /** @param array<string, mixed> $data */
    private static function assertString(array $data, string $key): void
    {
        if (!array_key_exists($key, $data) || !is_string($data[$key])) {
            throw new ValidationError(message: "Missing or non-string field '{$key}' in Campaign response");
        }
    }

    /** @param array<string, mixed> $data */
    private static function assertStringOrNull(array $data, string $key): void
    {
        if (!array_key_exists($key, $data)) {
            throw new ValidationError(message: "Missing field '{$key}' in Campaign response");
        }
        if ($data[$key] !== null && !is_string($data[$key])) {
            throw new ValidationError(message: "Field '{$key}' must be string or null in Campaign response");
        }
    }

    /** @param array<string, mixed> $data */
    private static function assertInt(array $data, string $key): void
    {
        if (!array_key_exists($key, $data) || !is_int($data[$key])) {
            throw new ValidationError(message: "Missing or non-int field '{$key}' in Campaign response");
        }
    }
}
