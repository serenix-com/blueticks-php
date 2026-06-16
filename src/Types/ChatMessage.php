<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

final class ChatMessage
{
    public function __construct(
        public readonly string $key,
        public readonly string $chatId,
        public readonly string $from,
        public readonly ?string $timestamp,
        public readonly ?string $text,
        public readonly string $type,
        public readonly bool $fromMe,
        public readonly ?int $ack,
        public readonly ?string $mediaUrl,
        public readonly ?string $caption,
        public readonly ?string $filename,
    ) {
    }

    /**
     * @param array<string, mixed> $raw
     */
    public static function fromArray(array $raw): self
    {
        self::assertString($raw, 'key');
        self::assertString($raw, 'chatId');
        self::assertString($raw, 'from');
        self::assertStringOrNull($raw, 'timestamp');
        self::assertStringOrNull($raw, 'text');
        self::assertString($raw, 'type');
        if (!array_key_exists('fromMe', $raw) || !is_bool($raw['fromMe'])) {
            throw new ValidationError(message: "Missing or non-bool field 'fromMe' in ChatMessage response");
        }
        if (array_key_exists('ack', $raw) && $raw['ack'] !== null && !is_int($raw['ack'])) {
            throw new ValidationError(message: "Field 'ack' must be int or null in ChatMessage response");
        }
        self::assertStringOrNull($raw, 'mediaUrl');
        self::assertStringOrNull($raw, 'caption');
        self::assertStringOrNull($raw, 'filename');

        return new self(
            key: $raw['key'],
            chatId: $raw['chatId'],
            from: $raw['from'],
            timestamp: $raw['timestamp'] ?? null,
            text: $raw['text'] ?? null,
            type: $raw['type'],
            fromMe: $raw['fromMe'],
            ack: $raw['ack'] ?? null,
            mediaUrl: $raw['mediaUrl'] ?? null,
            caption: $raw['caption'] ?? null,
            filename: $raw['filename'] ?? null,
        );
    }

    /** @param array<string, mixed> $data */
    private static function assertString(array $data, string $key): void
    {
        if (!array_key_exists($key, $data) || !is_string($data[$key])) {
            throw new ValidationError(message: "Missing or non-string field '{$key}' in ChatMessage response");
        }
    }

    /** @param array<string, mixed> $data */
    private static function assertStringOrNull(array $data, string $key): void
    {
        if (array_key_exists($key, $data) && $data[$key] !== null && !is_string($data[$key])) {
            throw new ValidationError(message: "Field '{$key}' must be string or null in ChatMessage response");
        }
    }
}
