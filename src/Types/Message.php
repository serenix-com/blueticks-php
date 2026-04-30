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
        public readonly ?string $media_url,
        public readonly string $status,
        public readonly ?string $send_at,
        public readonly string $created_at,
        public readonly ?string $sent_at,
        public readonly ?string $delivered_at,
        public readonly ?string $read_at,
        public readonly ?string $failed_at,
        public readonly ?string $failure_reason,
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
            media_url: $raw['media_url'],
            status: $raw['status'],
            send_at: $raw['send_at'],
            created_at: $raw['created_at'],
            sent_at: $raw['sent_at'],
            delivered_at: $raw['delivered_at'],
            read_at: $raw['read_at'],
            failed_at: $raw['failed_at'],
            failure_reason: $raw['failure_reason'],
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
