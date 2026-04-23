<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

final class Message
{
    public function __construct(
        public readonly string $id,
        public readonly string $to,
        public readonly ?string $from,
        public readonly ?string $text,
        public readonly ?string $mediaUrl,
        public readonly string $status,
        public readonly ?string $sendAt,
        public readonly string $createdAt,
        public readonly ?string $sentAt,
        public readonly ?string $deliveredAt,
        public readonly ?string $readAt,
        public readonly ?string $failedAt,
        public readonly ?string $failureReason,
    ) {
    }

    /**
     * @param array<string, mixed> $raw
     */
    public static function fromArray(array $raw): self
    {
        self::assertString($raw, 'id');
        self::assertString($raw, 'to');
        self::assertStringOrNull($raw, 'from');
        self::assertStringOrNull($raw, 'text');
        self::assertStringOrNull($raw, 'media_url');
        self::assertString($raw, 'status');
        self::assertStringOrNull($raw, 'send_at');
        self::assertString($raw, 'created_at');
        self::assertStringOrNull($raw, 'sent_at');
        self::assertStringOrNull($raw, 'delivered_at');
        self::assertStringOrNull($raw, 'read_at');
        self::assertStringOrNull($raw, 'failed_at');
        self::assertStringOrNull($raw, 'failure_reason');

        return new self(
            id: $raw['id'],
            to: $raw['to'],
            from: $raw['from'],
            text: $raw['text'],
            mediaUrl: $raw['media_url'],
            status: $raw['status'],
            sendAt: $raw['send_at'],
            createdAt: $raw['created_at'],
            sentAt: $raw['sent_at'],
            deliveredAt: $raw['delivered_at'],
            readAt: $raw['read_at'],
            failedAt: $raw['failed_at'],
            failureReason: $raw['failure_reason'],
        );
    }

    /** @param array<string, mixed> $data */
    private static function assertString(array $data, string $key): void
    {
        if (!array_key_exists($key, $data) || !is_string($data[$key])) {
            throw new ValidationError(message: "Missing or non-string field '{$key}' in Message response");
        }
    }

    /** @param array<string, mixed> $data */
    private static function assertStringOrNull(array $data, string $key): void
    {
        if (!array_key_exists($key, $data)) {
            throw new ValidationError(message: "Missing field '{$key}' in Message response");
        }
        if ($data[$key] !== null && !is_string($data[$key])) {
            throw new ValidationError(message: "Field '{$key}' must be string or null in Message response");
        }
    }
}
