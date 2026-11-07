<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * Result of removing a group picture. `ok` is a plain bool per the spec.
 */
final class GroupPictureRemoved
{
    public function __construct(
        public readonly bool $ok,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        if (!array_key_exists('ok', $data) || !is_bool($data['ok'])) {
            throw new ValidationError(message: "Missing or non-bool field 'ok' in GroupPictureRemoved response");
        }

        return new self(ok: $data['ok']);
    }
}
