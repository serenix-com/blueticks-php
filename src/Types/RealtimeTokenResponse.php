<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * MQTT credentials for receiving messages in real time, returned by `POST
 * /v1/realtime/token`.
 */
final class RealtimeTokenResponse
{
    public function __construct(
        public readonly string $brokerUrl,
        public readonly string $username,
        public readonly string $password,
        public readonly string $expiresAt,
        public readonly int $expiresInSeconds,
        public readonly RealtimeTokenTopics $topics,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $topicsData = self::reqMap($data, 'topics');

        return new self(
            brokerUrl: self::reqString($data, 'brokerUrl'),
            username: self::reqString($data, 'username'),
            password: self::reqString($data, 'password'),
            expiresAt: self::reqString($data, 'expiresAt'),
            expiresInSeconds: self::reqInt($data, 'expiresInSeconds'),
            topics: RealtimeTokenTopics::fromArray($topicsData),
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function reqInt(array $data, string $key): int
    {
        if (!array_key_exists($key, $data) || !is_int($data[$key])) {
            throw new ValidationError(
                message: "Missing or non-int field '{$key}' in RealtimeTokenResponse response",
            );
        }
        return $data[$key];
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private static function reqMap(array $data, string $key): array
    {
        if (!array_key_exists($key, $data) || !is_array($data[$key])) {
            throw new ValidationError(
                message: "Missing or non-object field '{$key}' in RealtimeTokenResponse response",
            );
        }
        /** @var array<string, mixed> $value */
        $value = $data[$key];
        return $value;
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function reqString(array $data, string $key): string
    {
        if (!array_key_exists($key, $data) || !is_string($data[$key])) {
            throw new ValidationError(
                message: "Missing or non-string field '{$key}' in RealtimeTokenResponse response",
            );
        }
        return $data[$key];
    }
}
