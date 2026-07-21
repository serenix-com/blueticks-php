<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * Media bytes (or hosted URL) for a message, returned by
 * `GET /v1/messages/media/{waMessageKey}`.
 */
final class ChatMedia
{
    public const MEDIA_UNAVAILABLE_REASONS = ['expired', 'fetching', 'awaiting_sender', 'error', 'no_media'];

    public function __construct(
        public readonly ?string $url,
        public readonly ?string $mimetype,
        public readonly ?string $filename,
        public readonly ?string $dataBase64,
        public readonly ?bool $originalQuality,
        public readonly ?string $mediaUnavailable,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        self::assertStringOrNull($data, 'url');
        self::assertStringOrNull($data, 'mimetype');
        self::assertStringOrNull($data, 'filename');
        self::assertStringOrNull($data, 'dataBase64');
        if (
            array_key_exists('originalQuality', $data)
            && $data['originalQuality'] !== null
            && !is_bool($data['originalQuality'])
        ) {
            throw new ValidationError(
                message: "Field 'originalQuality' must be bool or null in ChatMedia response",
            );
        }
        if (array_key_exists('mediaUnavailable', $data) && $data['mediaUnavailable'] !== null) {
            if (
                !is_string($data['mediaUnavailable'])
                || !in_array($data['mediaUnavailable'], self::MEDIA_UNAVAILABLE_REASONS, true)
            ) {
                throw new ValidationError(
                    message: "Field 'mediaUnavailable' must be one of "
                        . implode('/', self::MEDIA_UNAVAILABLE_REASONS)
                        . " or null in ChatMedia response",
                );
            }
        }

        return new self(
            url: $data['url'] ?? null,
            mimetype: $data['mimetype'] ?? null,
            filename: $data['filename'] ?? null,
            dataBase64: $data['dataBase64'] ?? null,
            originalQuality: $data['originalQuality'] ?? null,
            mediaUnavailable: $data['mediaUnavailable'] ?? null,
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
