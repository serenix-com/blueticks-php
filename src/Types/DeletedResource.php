<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * Soft-delete acknowledgement returned by `DELETE /v1/audiences/{id}`,
 * `DELETE /v1/scheduled-messages/{id}`, and `DELETE /v1/webhooks/{id}`.
 * The server confirms the deletion with the resource id and a literal
 * `deleted: true` flag.
 */
final class DeletedResource
{
    public function __construct(
        public readonly string $id,
        public readonly bool $deleted,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        self::assertString($data, 'id');
        self::assertDeletedTrue($data, 'deleted');

        return new self(
            id: $data['id'],
            deleted: $data['deleted'],
        );
    }

    /** @param array<string, mixed> $data */
    private static function assertString(array $data, string $key): void
    {
        if (!array_key_exists($key, $data) || !is_string($data[$key])) {
            throw new ValidationError(
                message: "Missing or non-string field '{$key}' in DeletedResource response",
            );
        }
    }

    /** @param array<string, mixed> $data */
    private static function assertDeletedTrue(array $data, string $key): void
    {
        if (!array_key_exists($key, $data) || !is_bool($data[$key])) {
            throw new ValidationError(
                message: "Missing or non-bool field '{$key}' in DeletedResource response",
            );
        }
        if ($data[$key] !== true) {
            throw new ValidationError(
                message: "Field '{$key}' must be true in DeletedResource response",
            );
        }
    }
}
