<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

final class SunoSong
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
        self::assertString($data, 'id');
        self::assertEnum($data, 'status');
        self::assertStringOrNull($data, 'audioUrl');
        self::assertStringOrNull($data, 'imageUrl');
        self::assertStringOrNull($data, 'title');
        self::assertNumberOrNull($data, 'durationSec');
        self::assertStringOrNull($data, 'model');
        self::assertOptionalStringOrNull($data, 'errorType');
        self::assertOptionalStringOrNull($data, 'errorMessage');

        return new self(
            id: $data['id'],
            status: $data['status'],
            audioUrl: $data['audioUrl'],
            imageUrl: $data['imageUrl'],
            title: $data['title'],
            durationSec: $data['durationSec'] === null ? null : (float) $data['durationSec'],
            model: $data['model'],
            errorType: $data['errorType'] ?? null,
            errorMessage: $data['errorMessage'] ?? null,
        );
    }

    /** @param array<string, mixed> $data */
    private static function assertString(array $data, string $key): void
    {
        if (!array_key_exists($key, $data) || !is_string($data[$key])) {
            throw new ValidationError(message: "Missing or non-string field '{$key}' in SunoSong response");
        }
    }

    /** @param array<string, mixed> $data */
    private static function assertEnum(array $data, string $key): void
    {
        if (
            !array_key_exists($key, $data)
            || !is_string($data[$key])
            || !in_array($data[$key], self::STATUSES, true)
        ) {
            throw new ValidationError(message: "Missing or invalid enum field '{$key}' in SunoSong response");
        }
    }

    /** @param array<string, mixed> $data */
    private static function assertStringOrNull(array $data, string $key): void
    {
        if (!array_key_exists($key, $data)) {
            throw new ValidationError(message: "Missing field '{$key}' in SunoSong response");
        }
        if ($data[$key] !== null && !is_string($data[$key])) {
            throw new ValidationError(message: "Field '{$key}' must be string or null in SunoSong response");
        }
    }

    /** @param array<string, mixed> $data */
    private static function assertOptionalStringOrNull(array $data, string $key): void
    {
        if (array_key_exists($key, $data) && $data[$key] !== null && !is_string($data[$key])) {
            throw new ValidationError(message: "Field '{$key}' must be string or null in SunoSong response");
        }
    }

    /** @param array<string, mixed> $data */
    private static function assertNumberOrNull(array $data, string $key): void
    {
        if (!array_key_exists($key, $data)) {
            throw new ValidationError(message: "Missing field '{$key}' in SunoSong response");
        }
        if ($data[$key] !== null && !is_int($data[$key]) && !is_float($data[$key])) {
            throw new ValidationError(message: "Field '{$key}' must be number or null in SunoSong response");
        }
    }
}
