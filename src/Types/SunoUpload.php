<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

final class SunoUpload
{
    public function __construct(
        public readonly string $uploadId,
        public readonly string $status,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        self::assertString($data, 'uploadId');
        self::assertString($data, 'status');

        return new self(
            uploadId: $data['uploadId'],
            status: $data['status'],
        );
    }

    /** @param array<string, mixed> $data */
    private static function assertString(array $data, string $key): void
    {
        if (!array_key_exists($key, $data) || !is_string($data[$key])) {
            throw new ValidationError(message: "Missing or non-string field '{$key}' in SunoUpload response");
        }
    }
}
