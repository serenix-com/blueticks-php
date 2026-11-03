<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * One candidate matched to an existing board item.
 */
final class FindSimilarMatch
{
    public function __construct(
        public readonly int $candidate_index,
        public readonly string $item_id,
        public readonly string $text,
        public readonly float $score,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            candidate_index: self::reqInt($data, 'candidate_index'),
            item_id: self::reqString($data, 'item_id'),
            text: self::reqString($data, 'text'),
            score: self::reqFloat($data, 'score'),
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function reqFloat(array $data, string $key): float
    {
        if (!array_key_exists($key, $data) || (!is_int($data[$key]) && !is_float($data[$key]))) {
            throw new ValidationError(
                message: "Missing or non-number field '{$key}' in FindSimilarMatch response",
            );
        }
        return (float) $data[$key];
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function reqInt(array $data, string $key): int
    {
        if (!array_key_exists($key, $data) || !is_int($data[$key])) {
            throw new ValidationError(
                message: "Missing or non-int field '{$key}' in FindSimilarMatch response",
            );
        }
        return $data[$key];
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function reqString(array $data, string $key): string
    {
        if (!array_key_exists($key, $data) || !is_string($data[$key])) {
            throw new ValidationError(
                message: "Missing or non-string field '{$key}' in FindSimilarMatch response",
            );
        }
        return $data[$key];
    }
}
