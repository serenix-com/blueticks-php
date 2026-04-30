<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

final class Participant
{
    public function __construct(
        public readonly string $chat_id,
        public readonly bool $is_admin,
        public readonly ?bool $is_super_admin,
    ) {
    }

    /**
     * @param array<string, mixed> $raw
     */
    public static function fromArray(array $raw): self
    {
        if (!array_key_exists('chat_id', $raw) || !is_string($raw['chat_id'])) {
            throw new ValidationError(message: "Missing or non-string field 'chat_id' in Participant response");
        }
        if (!array_key_exists('is_admin', $raw) || !is_bool($raw['is_admin'])) {
            throw new ValidationError(message: "Missing or non-bool field 'is_admin' in Participant response");
        }
        if (
            array_key_exists('is_super_admin', $raw)
            && $raw['is_super_admin'] !== null
            && !is_bool($raw['is_super_admin'])
        ) {
            throw new ValidationError(
                message: "Field 'is_super_admin' must be bool or null in Participant response",
            );
        }

        return new self(
            chat_id: $raw['chat_id'],
            is_admin: $raw['is_admin'],
            is_super_admin: $raw['is_super_admin'] ?? null,
        );
    }
}
