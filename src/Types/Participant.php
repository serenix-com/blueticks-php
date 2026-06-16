<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

final class Participant
{
    public function __construct(
        public readonly string $chatId,
        public readonly bool $isAdmin,
        public readonly ?bool $isSuperAdmin,
    ) {
    }

    /**
     * @param array<string, mixed> $raw
     */
    public static function fromArray(array $raw): self
    {
        if (!array_key_exists('chatId', $raw) || !is_string($raw['chatId'])) {
            throw new ValidationError(message: "Missing or non-string field 'chatId' in Participant response");
        }
        if (!array_key_exists('isAdmin', $raw) || !is_bool($raw['isAdmin'])) {
            throw new ValidationError(message: "Missing or non-bool field 'isAdmin' in Participant response");
        }
        if (
            array_key_exists('isSuperAdmin', $raw)
            && $raw['isSuperAdmin'] !== null
            && !is_bool($raw['isSuperAdmin'])
        ) {
            throw new ValidationError(
                message: "Field 'isSuperAdmin' must be bool or null in Participant response",
            );
        }

        return new self(
            chatId: $raw['chatId'],
            isAdmin: $raw['isAdmin'],
            isSuperAdmin: $raw['isSuperAdmin'] ?? null,
        );
    }
}
