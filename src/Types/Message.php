<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

final class Message
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $key,
        public readonly string $to,
        public readonly ?string $from,
        public readonly string $type,
        public readonly ?string $text,
        public readonly ?string $media_url,
        public readonly ?string $media_kind,
        public readonly ?string $poll_question,
        public readonly string $status,
        public readonly ?string $send_at,
        public readonly string $created_at,
        public readonly ?string $confirmed_at,
        public readonly ?string $received_at,
        public readonly ?string $read_at,
        public readonly ?string $played_at,
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
        self::assertStringOrNull($raw, 'key');
        self::assertString($raw, 'to');
        self::assertStringOrNull($raw, 'from');
        self::assertString($raw, 'type');
        self::assertStringOrNull($raw, 'text');
        self::assertStringOrNull($raw, 'media_url');
        self::assertStringOrNull($raw, 'media_kind');
        self::assertStringOrNull($raw, 'poll_question');
        self::assertString($raw, 'status');
        self::assertStringOrNull($raw, 'send_at');
        self::assertString($raw, 'created_at');
        self::assertStringOrNull($raw, 'confirmed_at');
        self::assertStringOrNull($raw, 'received_at');
        self::assertStringOrNull($raw, 'read_at');
        self::assertStringOrNull($raw, 'played_at');
        self::assertStringOrNull($raw, 'failed_at');
        self::assertStringOrNull($raw, 'failure_reason');

        return new self(
            id: $raw['id'],
            key: $raw['key'],
            to: $raw['to'],
            from: $raw['from'],
            type: $raw['type'],
            text: $raw['text'],
            media_url: $raw['media_url'],
            media_kind: $raw['media_kind'],
            poll_question: $raw['poll_question'],
            status: $raw['status'],
            send_at: $raw['send_at'],
            created_at: $raw['created_at'],
            confirmed_at: $raw['confirmed_at'],
            received_at: $raw['received_at'],
            read_at: $raw['read_at'],
            played_at: $raw['played_at'],
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
