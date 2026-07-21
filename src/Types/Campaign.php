<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

final class Campaign
{
    public const STATUSES = [
        'pending',
        'running',
        'paused',
        'complete_sent',
        'complete_delivered',
        'aborted',
    ];

    public function __construct(
        public readonly string $cmpId,
        public readonly string $name,
        public readonly string $audienceId,
        public readonly string $status,
        public readonly int $totalCount,
        public readonly int $sentCount,
        public readonly int $deliveredCount,
        public readonly int $readCount,
        public readonly int $failedCount,
        public readonly string $createdAt,
        public readonly ?string $startedAt,
        public readonly ?string $completedAt,
        public readonly ?string $abortedAt,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        self::assertString($data, 'cmpId');
        self::assertString($data, 'name');
        self::assertString($data, 'audienceId');
        self::assertEnum($data, 'status', self::STATUSES);
        self::assertInt($data, 'totalCount');
        self::assertInt($data, 'sentCount');
        self::assertInt($data, 'deliveredCount');
        self::assertInt($data, 'readCount');
        self::assertInt($data, 'failedCount');
        self::assertString($data, 'createdAt');
        self::assertStringOrNull($data, 'startedAt');
        self::assertStringOrNull($data, 'completedAt');
        self::assertStringOrNull($data, 'abortedAt');

        return new self(
            cmpId: $data['cmpId'],
            name: $data['name'],
            audienceId: $data['audienceId'],
            status: $data['status'],
            totalCount: $data['totalCount'],
            sentCount: $data['sentCount'],
            deliveredCount: $data['deliveredCount'],
            readCount: $data['readCount'],
            failedCount: $data['failedCount'],
            createdAt: $data['createdAt'],
            startedAt: $data['startedAt'] ?? null,
            completedAt: $data['completedAt'] ?? null,
            abortedAt: $data['abortedAt'] ?? null,
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
        if (array_key_exists($key, $data) && $data[$key] !== null && !is_string($data[$key])) {
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

    /**
     * @param array<string, mixed> $data
     * @param list<string>         $allowed
     */
    private static function assertEnum(array $data, string $key, array $allowed): void
    {
        if (
            !array_key_exists($key, $data)
            || !is_string($data[$key])
            || !in_array($data[$key], $allowed, true)
        ) {
            throw new ValidationError(
                message: "Field '{$key}' must be one of " . implode('/', $allowed) . " in Campaign response",
            );
        }
    }
}
