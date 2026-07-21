<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * The result of sending or scheduling a message — returned by the
 * scheduled-messages endpoints and by `POST /v1/messages/{chatId}`.
 */
final class Message
{
    public const TYPES = ['text', 'media', 'poll'];

    public const MEDIA_KINDS = ['image', 'video', 'audio', 'document', 'sticker', 'voice', 'gif'];

    public const STATUSES = [
        'pending',
        'before-wa-send',
        'bt-sent',
        'sending',
        'sent',
        'sent_pending_ack',
        'confirmed',
        'delivered',
        'received',
        'read',
        'played',
        'cancelled',
        'error',
        'failed',
        'expired',
    ];

    /**
     * @param ?list<string> $pollOptions
     */
    public function __construct(
        public readonly ?string $id,
        public readonly ?WaMessageKey $waMessageKey,
        public readonly string $to,
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
        public readonly ?string $secret,
        public readonly ?LinkPreview $linkPreview,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        self::assertStringOrNull($data, 'id');
        $waMessageKey = null;
        if (array_key_exists('waMessageKey', $data) && $data['waMessageKey'] !== null) {
            if (!is_array($data['waMessageKey'])) {
                throw new ValidationError(message: "Field 'waMessageKey' must be an object in Message response");
            }
            /** @var array<string, mixed> $keyData */
            $keyData = $data['waMessageKey'];
            $waMessageKey = WaMessageKey::fromArray($keyData);
        }
        self::assertString($data, 'to');
        self::assertEnum($data, 'type', self::TYPES);
        self::assertStringOrNull($data, 'text');
        self::assertStringOrNull($data, 'mediaUrl');
        self::assertEnumOrNull($data, 'mediaKind', self::MEDIA_KINDS);
        self::assertStringOrNull($data, 'pollQuestion');
        $pollOptions = self::stringListOrNull($data, 'pollOptions');
        self::assertBoolOrNull($data, 'pollAllowMultiple');
        self::assertEnum($data, 'status', self::STATUSES);
        self::assertStringOrNull($data, 'sendAt');
        self::assertString($data, 'createdAt');
        self::assertStringOrNull($data, 'confirmedAt');
        self::assertStringOrNull($data, 'receivedAt');
        self::assertStringOrNull($data, 'readAt');
        self::assertStringOrNull($data, 'playedAt');
        self::assertStringOrNull($data, 'failedAt');
        self::assertStringOrNull($data, 'failureReason');
        self::assertStringOrNull($data, 'secret');

        $linkPreview = null;
        if (array_key_exists('linkPreview', $data) && $data['linkPreview'] !== null) {
            if (!is_array($data['linkPreview'])) {
                throw new ValidationError(message: "Field 'linkPreview' must be an object in Message response");
            }
            /** @var array<string, mixed> $lp */
            $lp = $data['linkPreview'];
            $linkPreview = LinkPreview::fromArray($lp);
        }

        return new self(
            id: $data['id'] ?? null,
            waMessageKey: $waMessageKey,
            to: $data['to'],
            type: $data['type'],
            text: $data['text'] ?? null,
            mediaUrl: $data['mediaUrl'] ?? null,
            mediaKind: $data['mediaKind'] ?? null,
            pollQuestion: $data['pollQuestion'] ?? null,
            pollOptions: $pollOptions,
            pollAllowMultiple: $data['pollAllowMultiple'] ?? null,
            status: $data['status'],
            sendAt: $data['sendAt'] ?? null,
            createdAt: $data['createdAt'],
            confirmedAt: $data['confirmedAt'] ?? null,
            receivedAt: $data['receivedAt'] ?? null,
            readAt: $data['readAt'] ?? null,
            playedAt: $data['playedAt'] ?? null,
            failedAt: $data['failedAt'] ?? null,
            failureReason: $data['failureReason'] ?? null,
            secret: $data['secret'] ?? null,
            linkPreview: $linkPreview,
        );
    }

    /**
     * @param array<string, mixed> $data
     * @return ?list<string>
     */
    private static function stringListOrNull(array $data, string $key): ?array
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
                throw new ValidationError(message: "Field '{$key}' must contain only strings in Message response");
            }
            $out[] = $item;
        }
        return $out;
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
        if (array_key_exists($key, $data) && $data[$key] !== null && !is_string($data[$key])) {
            throw new ValidationError(message: "Field '{$key}' must be string or null in Message response");
        }
    }

    /** @param array<string, mixed> $data */
    private static function assertBoolOrNull(array $data, string $key): void
    {
        if (array_key_exists($key, $data) && $data[$key] !== null && !is_bool($data[$key])) {
            throw new ValidationError(message: "Field '{$key}' must be bool or null in Message response");
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
                message: "Field '{$key}' must be one of " . implode('/', $allowed) . " in Message response",
            );
        }
    }

    /**
     * @param array<string, mixed> $data
     * @param list<string>         $allowed
     */
    private static function assertEnumOrNull(array $data, string $key, array $allowed): void
    {
        if (!array_key_exists($key, $data) || $data[$key] === null) {
            return;
        }
        if (!is_string($data[$key]) || !in_array($data[$key], $allowed, true)) {
            throw new ValidationError(
                message: "Field '{$key}' must be one of " . implode('/', $allowed) . " or null in Message response",
            );
        }
    }
}
