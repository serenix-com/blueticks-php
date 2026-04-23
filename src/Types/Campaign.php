<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

final class Campaign
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $audienceId,
        public readonly string $status,
        public readonly int $totalCount,
        public readonly int $sentCount,
        public readonly int $deliveredCount,
        public readonly int $readCount,
        public readonly int $failedCount,
        public readonly ?string $from,
        public readonly string $createdAt,
        public readonly ?string $startedAt,
        public readonly ?string $completedAt,
        public readonly ?string $abortedAt,
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
            audienceId: $raw['audience_id'],
            status: $raw['status'],
            totalCount: $raw['total_count'],
            sentCount: $raw['sent_count'],
            deliveredCount: $raw['delivered_count'],
            readCount: $raw['read_count'],
            failedCount: $raw['failed_count'],
            from: $raw['from'],
            createdAt: $raw['created_at'],
            startedAt: $raw['started_at'],
            completedAt: $raw['completed_at'],
            abortedAt: $raw['aborted_at'],
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
