<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

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
        self::assertNumber($data, 'creditsLeft');
        self::assertNumberOrNull($data, 'monthlyLimit');
        self::assertNumberOrNull($data, 'monthlyUsage');
        self::assertStringOrNull($data, 'plan');

        return new self(
            creditsLeft: (float) $data['creditsLeft'],
            monthlyLimit: $data['monthlyLimit'] === null ? null : (float) $data['monthlyLimit'],
            monthlyUsage: $data['monthlyUsage'] === null ? null : (float) $data['monthlyUsage'],
            plan: $data['plan'],
        );
    }

    /** @param array<string, mixed> $data */
    private static function assertNumber(array $data, string $key): void
    {
        if (!array_key_exists($key, $data) || (!is_int($data[$key]) && !is_float($data[$key]))) {
            throw new ValidationError(message: "Missing or non-number field '{$key}' in SunoAccount response");
        }
    }

    /** @param array<string, mixed> $data */
    private static function assertNumberOrNull(array $data, string $key): void
    {
        if (!array_key_exists($key, $data)) {
            throw new ValidationError(message: "Missing field '{$key}' in SunoAccount response");
        }
        if ($data[$key] !== null && !is_int($data[$key]) && !is_float($data[$key])) {
            throw new ValidationError(message: "Field '{$key}' must be number or null in SunoAccount response");
        }
    }

    /** @param array<string, mixed> $data */
    private static function assertStringOrNull(array $data, string $key): void
    {
        if (!array_key_exists($key, $data)) {
            throw new ValidationError(message: "Missing field '{$key}' in SunoAccount response");
        }
        if ($data[$key] !== null && !is_string($data[$key])) {
            throw new ValidationError(message: "Field '{$key}' must be string or null in SunoAccount response");
        }
    }
}
