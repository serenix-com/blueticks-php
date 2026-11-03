<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * Result of a server-side dedupe check: matched candidates and the indexes of candidates
 * with no match.
 */
final class FindSimilarResponse
{
    /**
     * @param list<FindSimilarMatch> $matches
     * @param list<int> $misses
     */
    public function __construct(
        public readonly array $matches,
        public readonly array $misses,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $matches = [];
        foreach (self::reqList($data, 'matches') as $row) {
            if (!is_array($row)) {
                throw new ValidationError(
                    message: "Field 'matches' must contain only objects in FindSimilarResponse response",
                );
            }
            /** @var array<string, mixed> $row */
            $matches[] = FindSimilarMatch::fromArray($row);
        }

        return new self(
            matches: $matches,
            misses: self::reqIntList($data, 'misses'),
        );
    }

    /**
     * @param array<string, mixed> $data
     * @return list<int>
     */
    private static function reqIntList(array $data, string $key): array
    {
        if (!array_key_exists($key, $data) || !is_array($data[$key])) {
            throw new ValidationError(
                message: "Missing or non-array field '{$key}' in FindSimilarResponse response",
            );
        }
        $out = [];
        foreach ($data[$key] as $item) {
            if (!is_int($item)) {
                throw new ValidationError(
                    message: "Field '{$key}' must contain only integers in FindSimilarResponse response",
                );
            }
            $out[] = $item;
        }
        return $out;
    }

    /**
     * @param array<string, mixed> $data
     * @return list<mixed>
     */
    private static function reqList(array $data, string $key): array
    {
        if (!array_key_exists($key, $data) || !is_array($data[$key])) {
            throw new ValidationError(
                message: "Missing or non-array field '{$key}' in FindSimilarResponse response",
            );
        }
        return array_values($data[$key]);
    }
}
