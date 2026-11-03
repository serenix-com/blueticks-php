<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * A board option (status/priority label and its color).
 */
final class OptionRef
{
    public function __construct(
        public readonly string $label,
        public readonly string $bgcolor,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            label: self::reqString($data, 'label'),
            bgcolor: self::reqString($data, 'bgcolor'),
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function reqString(array $data, string $key): string
    {
        if (!array_key_exists($key, $data) || !is_string($data[$key])) {
            throw new ValidationError(
                message: "Missing or non-string field '{$key}' in OptionRef response",
            );
        }
        return $data[$key];
    }
}
