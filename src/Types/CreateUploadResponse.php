<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * Upload link returned by `POST /v1/uploads`. PUT the raw file bytes to
 * `uploadUrl` with exactly the `headers`, then pass `fileId` as
 * `mediaFileId` on a media send.
 */
final class CreateUploadResponse
{
    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        public readonly string $fileId,
        public readonly string $uploadUrl,
        public readonly string $method,
        public readonly array $headers,
        public readonly string $expiresAt,
        public readonly string $deleteAt,
        public readonly float $maxBytes,
        public readonly string $contentType,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        self::assertString($data, 'fileId');
        self::assertString($data, 'uploadUrl');
        self::assertString($data, 'method');
        if ($data['method'] !== 'PUT') {
            throw new ValidationError(message: "Field 'method' must be 'PUT' in CreateUploadResponse response");
        }
        $headers = self::headers($data);
        self::assertString($data, 'expiresAt');
        self::assertString($data, 'deleteAt');
        self::assertNumber($data, 'maxBytes');
        self::assertString($data, 'contentType');

        return new self(
            fileId: $data['fileId'],
            uploadUrl: $data['uploadUrl'],
            method: $data['method'],
            headers: $headers,
            expiresAt: $data['expiresAt'],
            deleteAt: $data['deleteAt'],
            maxBytes: (float) $data['maxBytes'],
            contentType: $data['contentType'],
        );
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, string>
     */
    private static function headers(array $data): array
    {
        if (!array_key_exists('headers', $data) || !is_array($data['headers'])) {
            throw new ValidationError(
                message: "Missing or non-object field 'headers' in CreateUploadResponse response",
            );
        }
        $out = [];
        foreach ($data['headers'] as $name => $value) {
            if (!is_string($name) || !is_string($value)) {
                throw new ValidationError(
                    message: "Field 'headers' must map strings to strings in CreateUploadResponse response",
                );
            }
            $out[$name] = $value;
        }
        return $out;
    }

    /** @param array<string, mixed> $data */
    private static function assertString(array $data, string $key): void
    {
        if (!array_key_exists($key, $data) || !is_string($data[$key])) {
            throw new ValidationError(message: "Missing or non-string field '{$key}' in CreateUploadResponse response");
        }
    }

    /** @param array<string, mixed> $data */
    private static function assertNumber(array $data, string $key): void
    {
        if (!array_key_exists($key, $data) || !(is_int($data[$key]) || is_float($data[$key]))) {
            throw new ValidationError(
                message: "Missing or non-numeric field '{$key}' in CreateUploadResponse response",
            );
        }
    }
}
