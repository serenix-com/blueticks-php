<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * Generic acknowledgement envelope returned by mutation endpoints that
 * carry no payload of their own. Always `{ "ok": true }` on success.
 */
final class OkResponse
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
            throw new ValidationError(message: "Missing or non-bool field 'ok' in OkResponse response");
        }
        if ($data['ok'] !== true) {
            throw new ValidationError(message: "Field 'ok' must be true in OkResponse response");
        }

        return new self(ok: $data['ok']);
    }
}
