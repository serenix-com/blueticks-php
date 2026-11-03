<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * Compact board reference embedded in bulk-upsert results.
 */
final class BoardRef
{
    public function __construct(
        public readonly string $board_id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?string $domain,
        public readonly string $web_url,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            board_id: self::reqString($data, 'board_id'),
            name: self::reqString($data, 'name'),
            description: self::optString($data, 'description'),
            domain: self::optString($data, 'domain'),
            web_url: self::reqString($data, 'web_url'),
        );
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
                message: "Field '{$key}' must be string or null in BoardRef response",
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
                message: "Missing or non-string field '{$key}' in BoardRef response",
            );
        }
        return $data[$key];
    }
}
