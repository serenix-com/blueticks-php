<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

final class ChatMedia
{
    public const MEDIA_UNAVAILABLE_REASONS = ['expired', 'fetching', 'awaiting_sender', 'error', 'no_media'];

    public function __construct(
        public readonly ?string $url,
        public readonly ?string $mimetype,
        public readonly ?string $filename,
        public readonly ?string $dataBase64,
        /** false when WA returned a preview JPEG instead of the original
         *  (#113 — own-sent newsletter media only). null/absent when the
         *  bytes are the genuine original from the sender. */
        public readonly ?bool $originalQuality,
        /** Reason the bytes couldn't be retrieved. One of
         *  {@see ChatMedia::MEDIA_UNAVAILABLE_REASONS}. null/absent on
         *  success. */
        public readonly ?string $mediaUnavailable,
    ) {
    }

    /**
     * @param array<string, mixed> $raw
     */
    public static function fromArray(array $raw): self
    {
        self::assertStringOrNull($raw, 'url');
        self::assertStringOrNull($raw, 'mimetype');
        self::assertStringOrNull($raw, 'filename');
        self::assertStringOrNull($raw, 'dataBase64');
        if (
            array_key_exists('originalQuality', $raw)
            && $raw['originalQuality'] !== null
            && !is_bool($raw['originalQuality'])
        ) {
            throw new ValidationError(
                message: "Field 'originalQuality' must be bool or null in ChatMedia response",
            );
        }
        if (array_key_exists('mediaUnavailable', $raw) && $raw['mediaUnavailable'] !== null) {
            if (!is_string($raw['mediaUnavailable'])) {
                throw new ValidationError(
                    message: "Field 'mediaUnavailable' must be string or null in ChatMedia response",
                );
            }
            if (!in_array($raw['mediaUnavailable'], self::MEDIA_UNAVAILABLE_REASONS, true)) {
                throw new ValidationError(
                    message: "Field 'mediaUnavailable' must be one of "
                        . implode('/', self::MEDIA_UNAVAILABLE_REASONS)
                        . " in ChatMedia response",
                );
            }
        }

        return new self(
            url: $raw['url'] ?? null,
            mimetype: $raw['mimetype'] ?? null,
            filename: $raw['filename'] ?? null,
            dataBase64: $raw['dataBase64'] ?? null,
            originalQuality: $raw['originalQuality'] ?? null,
            mediaUnavailable: $raw['mediaUnavailable'] ?? null,
        );
    }

    /** @param array<string, mixed> $data */
    private static function assertStringOrNull(array $data, string $key): void
    {
        if (array_key_exists($key, $data) && $data[$key] !== null && !is_string($data[$key])) {
            throw new ValidationError(message: "Field '{$key}' must be string or null in ChatMedia response");
        }
    }
}
