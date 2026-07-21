<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * Result of appending contacts to an audience via
 * `POST /v1/audiences/{id}/contacts`.
 */
final class AppendContactsResult
{
    public function __construct(
        public readonly int $added,
        public readonly int $contactCount,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        if (!array_key_exists('added', $data) || !is_int($data['added'])) {
            throw new ValidationError(message: "Missing or non-int field 'added' in AppendContactsResult response");
        }
        if (!array_key_exists('contactCount', $data) || !is_int($data['contactCount'])) {
            throw new ValidationError(
                message: "Missing or non-int field 'contactCount' in AppendContactsResult response",
            );
        }

        return new self(
            added: $data['added'],
            contactCount: $data['contactCount'],
        );
    }
}
