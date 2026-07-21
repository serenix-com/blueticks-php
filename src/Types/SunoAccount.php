<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * Connected Suno account usage, returned by `GET /v1/suno/account`.
 */
final class SunoAccount
{
    public function __construct(
        public readonly float $creditsLeft,
        public readonly ?float $monthlyLimit,
        public readonly ?float $monthlyUsage,
        public readonly ?string $plan,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            creditsLeft: self::requireFloat($data, 'creditsLeft'),
            monthlyLimit: self::floatOrNull($data, 'monthlyLimit'),
            monthlyUsage: self::floatOrNull($data, 'monthlyUsage'),
            plan: self::stringOrNull($data, 'plan'),
        );
    }

    /** @param array<string, mixed> $data */
    private static function requireFloat(array $data, string $key): float
    {
        if (!array_key_exists($key, $data) || (!is_float($data[$key]) && !is_int($data[$key]))) {
            throw new ValidationError(message: "Missing or non-number field '{$key}' in SunoAccount response");
        }
        return (float) $data[$key];
    }

    /** @param array<string, mixed> $data */
    private static function floatOrNull(array $data, string $key): ?float
    {
        if (!array_key_exists($key, $data) || $data[$key] === null) {
            return null;
        }
        if (!is_float($data[$key]) && !is_int($data[$key])) {
            throw new ValidationError(message: "Field '{$key}' must be number or null in SunoAccount response");
        }
        return (float) $data[$key];
    }

    /** @param array<string, mixed> $data */
    private static function stringOrNull(array $data, string $key): ?string
    {
        if (!array_key_exists($key, $data) || $data[$key] === null) {
            return null;
        }
        if (!is_string($data[$key])) {
            throw new ValidationError(message: "Field '{$key}' must be string or null in SunoAccount response");
        }
        return $data[$key];
    }
}
