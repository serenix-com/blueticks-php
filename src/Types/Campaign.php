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
        self::assertString($raw, 'audienceId');
        self::assertString($raw, 'status');
        self::assertInt($raw, 'totalCount');
        self::assertInt($raw, 'sentCount');
        self::assertInt($raw, 'deliveredCount');
        self::assertInt($raw, 'readCount');
        self::assertInt($raw, 'failedCount');
        self::assertStringOrNull($raw, 'from');
        self::assertString($raw, 'createdAt');
        self::assertStringOrNull($raw, 'startedAt');
        self::assertStringOrNull($raw, 'completedAt');
        self::assertStringOrNull($raw, 'abortedAt');

        return new self(
            id: $raw['id'],
            name: $raw['name'],
            audienceId: $raw['audienceId'],
            status: $raw['status'],
            totalCount: $raw['totalCount'],
            sentCount: $raw['sentCount'],
            deliveredCount: $raw['deliveredCount'],
            readCount: $raw['readCount'],
            failedCount: $raw['failedCount'],
            from: $raw['from'],
            createdAt: $raw['createdAt'],
            startedAt: $raw['startedAt'],
            completedAt: $raw['completedAt'],
            abortedAt: $raw['abortedAt'],
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
