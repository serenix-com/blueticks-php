<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

final class ScheduledMessage
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $to,
        public readonly ?string $text,
        public readonly ?string $media_url,
        public readonly ?string $media_caption,
        public readonly ?string $media_filename,
        public readonly ?string $media_mime_type,
        public readonly ?string $send_at,
        public readonly ?string $status,
        public readonly bool $is_recurring,
        public readonly ?string $recurrence_rule,
        public readonly ?string $created_at,
        public readonly ?string $updated_at,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        self::assertString($data, 'id');
        self::assertStringOrNull($data, 'to');
        self::assertStringOrNull($data, 'text');
        self::assertStringOrNull($data, 'media_url');
        self::assertStringOrNull($data, 'media_caption');
        self::assertStringOrNull($data, 'media_filename');
        self::assertStringOrNull($data, 'media_mime_type');
        self::assertStringOrNull($data, 'send_at');
        self::assertStringOrNull($data, 'status');
        self::assertBool($data, 'is_recurring');
        self::assertStringOrNull($data, 'recurrence_rule');
        self::assertStringOrNull($data, 'created_at');
        self::assertStringOrNull($data, 'updated_at');

        /** @var string $id */
        $id = $data['id'];
        /** @var ?string $to */
        $to = $data['to'];
        /** @var ?string $text */
        $text = $data['text'];
        /** @var ?string $mediaUrl */
        $mediaUrl = $data['media_url'];
        /** @var ?string $mediaCaption */
        $mediaCaption = $data['media_caption'];
        /** @var ?string $mediaFilename */
        $mediaFilename = $data['media_filename'];
        /** @var ?string $mediaMimeType */
        $mediaMimeType = $data['media_mime_type'];
        /** @var ?string $sendAt */
        $sendAt = $data['send_at'];
        /** @var ?string $status */
        $status = $data['status'];
        /** @var bool $isRecurring */
        $isRecurring = $data['is_recurring'];
        /** @var ?string $recurrenceRule */
        $recurrenceRule = $data['recurrence_rule'];
        /** @var ?string $createdAt */
        $createdAt = $data['created_at'];
        /** @var ?string $updatedAt */
        $updatedAt = $data['updated_at'];

        return new self(
            id: $id,
            to: $to,
            text: $text,
            media_url: $mediaUrl,
            media_caption: $mediaCaption,
            media_filename: $mediaFilename,
            media_mime_type: $mediaMimeType,
            send_at: $sendAt,
            status: $status,
            is_recurring: $isRecurring,
            recurrence_rule: $recurrenceRule,
            created_at: $createdAt,
            updated_at: $updatedAt,
        );
    }

    /** @param array<string, mixed> $data */
    private static function assertString(array $data, string $key): void
    {
        if (!array_key_exists($key, $data) || !is_string($data[$key])) {
            throw new ValidationError(message: "Missing or non-string field '{$key}' in ScheduledMessage response");
        }
    }

    /** @param array<string, mixed> $data */
    private static function assertStringOrNull(array $data, string $key): void
    {
        if (!array_key_exists($key, $data)) {
            throw new ValidationError(message: "Missing field '{$key}' in ScheduledMessage response");
        }
        if ($data[$key] !== null && !is_string($data[$key])) {
            throw new ValidationError(message: "Field '{$key}' must be string or null in ScheduledMessage response");
        }
    }

    /** @param array<string, mixed> $data */
    private static function assertBool(array $data, string $key): void
    {
        if (!array_key_exists($key, $data) || !is_bool($data[$key])) {
            throw new ValidationError(message: "Missing or non-bool field '{$key}' in ScheduledMessage response");
        }
    }
}
