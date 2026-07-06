<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

final class Message
{
    /**
     * @param ?list<string> $pollOptions
     */
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
        public readonly ?array $pollOptions,
        public readonly ?bool $pollAllowMultiple,
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
        $pollOptions = self::readStringListOrNull($raw, 'pollOptions');
        if (
            array_key_exists('pollAllowMultiple', $raw)
            && $raw['pollAllowMultiple'] !== null
            && !is_bool($raw['pollAllowMultiple'])
        ) {
            throw new ValidationError(
                message: "Field 'pollAllowMultiple' must be bool or null in Message response",
            );
        }
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
            pollOptions: $pollOptions,
            pollAllowMultiple: $raw['pollAllowMultiple'] ?? null,
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

    /**
     * @param array<string, mixed> $data
     * @return ?list<string>
     */
    private static function readStringListOrNull(array $data, string $key): ?array
    {
        if (!array_key_exists($key, $data) || $data[$key] === null) {
            return null;
        }
        if (!is_array($data[$key])) {
            throw new ValidationError(message: "Field '{$key}' must be array or null in Message response");
        }
        $out = [];
        foreach ($data[$key] as $item) {
            if (!is_string($item)) {
                throw new ValidationError(message: "Each entry of '{$key}' must be a string in Message response");
            }
            $out[] = $item;
        }
        return $out;
    }
}
