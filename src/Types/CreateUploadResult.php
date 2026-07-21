<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * Result of uploading reference audio via `POST /v1/suno/uploads`.
 */
final class CreateUploadResult
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
        if (!array_key_exists('uploadId', $data) || !is_string($data['uploadId'])) {
            throw new ValidationError(
                message: "Missing or non-string field 'uploadId' in CreateUploadResult response",
            );
        }
        if (!array_key_exists('status', $data) || !is_string($data['status'])) {
            throw new ValidationError(message: "Missing or non-string field 'status' in CreateUploadResult response");
        }

        return new self(
            uploadId: $data['uploadId'],
            status: $data['status'],
        );
    }
}
