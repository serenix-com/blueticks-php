<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * How far back a chat is now loaded (`POST /v1/messages/load_older/{chatId}` `coverage`).
 */
final class LoadCoverage
{
    public function __construct(
        public readonly ?float $oldestLoadedTimestamp,
        public readonly ?string $oldestLoadedIso,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            oldestLoadedTimestamp: self::optFloat($data, 'oldestLoadedTimestamp'),
            oldestLoadedIso: self::optString($data, 'oldestLoadedIso'),
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function optFloat(array $data, string $key): ?float
    {
        if (!array_key_exists($key, $data) || $data[$key] === null) {
            return null;
        }
        if (!is_int($data[$key]) && !is_float($data[$key])) {
            throw new ValidationError(
                message: "Field '{$key}' must be number or null in LoadCoverage response",
            );
        }
        return (float) $data[$key];
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function optString(array $data, string $key): ?string
    {
        if (!array_key_exists($key, $data) || $data[$key] === null) {
            return null;
        }
        if (!is_string($data[$key])) {
            throw new ValidationError(
                message: "Field '{$key}' must be string or null in LoadCoverage response",
            );
        }
        return $data[$key];
    }
}
