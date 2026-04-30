<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

final class ChatMedia
{
    public const MEDIA_UNAVAILABLE_REASONS = ['expired', 'fetching', 'error', 'no_media'];

    public function __construct(
        public readonly ?string $url,
        public readonly ?string $mimetype,
        public readonly ?string $filename,
        public readonly ?string $data_base64,
        /** false when WA returned a preview JPEG instead of the original
         *  (#113 — own-sent newsletter media only). null/absent when the
         *  bytes are the genuine original from the sender. */
        public readonly ?bool $original_quality,
        /** Reason the bytes couldn't be retrieved. One of
         *  {@see ChatMedia::MEDIA_UNAVAILABLE_REASONS}. null/absent on
         *  success. */
        public readonly ?string $media_unavailable,
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
        self::assertStringOrNull($raw, 'data_base64');
        if (
            array_key_exists('original_quality', $raw)
            && $raw['original_quality'] !== null
            && !is_bool($raw['original_quality'])
        ) {
            throw new ValidationError(
                message: "Field 'original_quality' must be bool or null in ChatMedia response",
            );
        }
        if (array_key_exists('media_unavailable', $raw) && $raw['media_unavailable'] !== null) {
            if (!is_string($raw['media_unavailable'])) {
                throw new ValidationError(
                    message: "Field 'media_unavailable' must be string or null in ChatMedia response",
                );
            }
            if (!in_array($raw['media_unavailable'], self::MEDIA_UNAVAILABLE_REASONS, true)) {
                throw new ValidationError(
                    message: "Field 'media_unavailable' must be one of "
                        . implode('/', self::MEDIA_UNAVAILABLE_REASONS)
                        . " in ChatMedia response",
                );
            }
        }

        return new self(
            url: $raw['url'] ?? null,
            mimetype: $raw['mimetype'] ?? null,
            filename: $raw['filename'] ?? null,
            data_base64: $raw['data_base64'] ?? null,
            original_quality: $raw['original_quality'] ?? null,
            media_unavailable: $raw['media_unavailable'] ?? null,
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
