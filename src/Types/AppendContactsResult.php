<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

final class AppendContactsResult
{
    public function __construct(
        public readonly int $added,
        public readonly int $contactCount,
    ) {
    }

    /**
     * @param array<string, mixed> $raw
     */
    public static function fromArray(array $raw): self
    {
        if (!array_key_exists('added', $raw) || !is_int($raw['added'])) {
            throw new ValidationError(message: "Missing or non-int field 'added' in AppendContactsResult response");
        }
        if (!array_key_exists('contactCount', $raw) || !is_int($raw['contactCount'])) {
            throw new ValidationError(
                message: "Missing or non-int field 'contactCount' in AppendContactsResult response"
            );
        }

        return new self(
            added: $raw['added'],
            contactCount: $raw['contactCount'],
        );
    }
}
