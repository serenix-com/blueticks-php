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
        public readonly ?string $mediaUrl,
        public readonly ?string $mediaKind,
        public readonly ?string $pollQuestion,
        public readonly string $status,
        public readonly ?string $sendAt,
        public readonly string $createdAt,
        public readonly ?string $confirmedAt,
        public readonly ?string $receivedAt,
        public readonly ?string $readAt,
        public readonly ?string $playedAt,
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
        self::assertStringOrNull($raw, 'key');
        self::assertString($raw, 'to');
        self::assertStringOrNull($raw, 'from');
        self::assertString($raw, 'type');
        self::assertStringOrNull($raw, 'text');
        self::assertStringOrNull($raw, 'mediaUrl');
        self::assertStringOrNull($raw, 'mediaKind');
        self::assertStringOrNull($raw, 'pollQuestion');
        self::assertString($raw, 'status');
        self::assertStringOrNull($raw, 'sendAt');
        self::assertString($raw, 'createdAt');
        self::assertStringOrNull($raw, 'confirmedAt');
        self::assertStringOrNull($raw, 'receivedAt');
        self::assertStringOrNull($raw, 'readAt');
        self::assertStringOrNull($raw, 'playedAt');
        self::assertStringOrNull($raw, 'failedAt');
        self::assertStringOrNull($raw, 'failureReason');

        return new self(
            id: $raw['id'],
            key: $raw['key'],
            to: $raw['to'],
            from: $raw['from'],
            type: $raw['type'],
            text: $raw['text'],
            mediaUrl: $raw['mediaUrl'],
            mediaKind: $raw['mediaKind'],
            pollQuestion: $raw['pollQuestion'],
            status: $raw['status'],
            sendAt: $raw['sendAt'],
            createdAt: $raw['createdAt'],
            confirmedAt: $raw['confirmedAt'],
            receivedAt: $raw['receivedAt'],
            readAt: $raw['readAt'],
            playedAt: $raw['playedAt'],
            failedAt: $raw['failedAt'],
            failureReason: $raw['failureReason'],
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
