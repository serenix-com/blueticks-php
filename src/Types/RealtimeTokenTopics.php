<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * MQTT topics to subscribe to with a realtime token.
 */
final class RealtimeTokenTopics
{
    public function __construct(
        public readonly string $messages,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            messages: self::reqString($data, 'messages'),
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function reqString(array $data, string $key): string
    {
        if (!array_key_exists($key, $data) || !is_string($data[$key])) {
            throw new ValidationError(
                message: "Missing or non-string field '{$key}' in RealtimeTokenTopics response",
            );
        }
        return $data[$key];
    }
}
