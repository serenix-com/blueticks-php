<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * One generated Suno clip — returned inside {@see GenerateSongResult} and
 * directly by `GET /v1/suno/songs/{id}`.
 */
final class SongClip
{
    public const STATUSES = ['submitted', 'queued', 'running', 'streaming', 'complete', 'error'];

    public function __construct(
        public readonly string $id,
        public readonly string $status,
        public readonly ?string $audioUrl,
        public readonly ?string $imageUrl,
        public readonly ?string $title,
        public readonly ?float $durationSec,
        public readonly ?string $model,
        public readonly ?string $errorType,
        public readonly ?string $errorMessage,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        if (!array_key_exists('id', $data) || !is_string($data['id'])) {
            throw new ValidationError(message: "Missing or non-string field 'id' in SongClip response");
        }
        if (
            !array_key_exists('status', $data)
            || !is_string($data['status'])
            || !in_array($data['status'], self::STATUSES, true)
        ) {
            throw new ValidationError(
                message: "Field 'status' must be one of " . implode('/', self::STATUSES) . " in SongClip response",
            );
        }
        self::assertStringOrNull($data, 'audioUrl');
        self::assertStringOrNull($data, 'imageUrl');
        self::assertStringOrNull($data, 'title');
        $durationSec = null;
        if (array_key_exists('durationSec', $data) && $data['durationSec'] !== null) {
            if (!is_float($data['durationSec']) && !is_int($data['durationSec'])) {
                throw new ValidationError(
                    message: "Field 'durationSec' must be number or null in SongClip response",
                );
            }
            $durationSec = (float) $data['durationSec'];
        }
        self::assertStringOrNull($data, 'model');
        self::assertStringOrNull($data, 'errorType');
        self::assertStringOrNull($data, 'errorMessage');

        return new self(
            id: $data['id'],
            status: $data['status'],
            audioUrl: $data['audioUrl'] ?? null,
            imageUrl: $data['imageUrl'] ?? null,
            title: $data['title'] ?? null,
            durationSec: $durationSec,
            model: $data['model'] ?? null,
            errorType: $data['errorType'] ?? null,
            errorMessage: $data['errorMessage'] ?? null,
        );
    }

    /** @param array<string, mixed> $data */
    private static function assertStringOrNull(array $data, string $key): void
    {
        if (array_key_exists($key, $data) && $data[$key] !== null && !is_string($data[$key])) {
            throw new ValidationError(message: "Field '{$key}' must be string or null in SongClip response");
        }
    }
}
