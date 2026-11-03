<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * Provenance of a board item (the chat message it was extracted from).
 */
final class ItemSource
{
    public function __construct(
        public readonly ?string $chat_id,
        public readonly ?string $chat_name,
        public readonly ?string $message_key,
        public readonly ?string $excerpt,
        public readonly ?int $extracted_at,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            chat_id: self::optString($data, 'chat_id'),
            chat_name: self::optString($data, 'chat_name'),
            message_key: self::optString($data, 'message_key'),
            excerpt: self::optString($data, 'excerpt'),
            extracted_at: self::optInt($data, 'extracted_at'),
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function optInt(array $data, string $key): ?int
    {
        if (!array_key_exists($key, $data) || $data[$key] === null) {
            return null;
        }
        if (!is_int($data[$key])) {
            throw new ValidationError(
                message: "Field '{$key}' must be int or null in ItemSource response",
            );
        }
        return $data[$key];
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
                message: "Field '{$key}' must be string or null in ItemSource response",
            );
        }
        return $data[$key];
    }
}
