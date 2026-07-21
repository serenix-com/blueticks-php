<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * A participant embedded in a full {@see Group} snapshot.
 */
final class GroupParticipant
{
    public function __construct(
        public readonly string $chatId,
        public readonly bool $isAdmin,
        public readonly bool $isSuperAdmin,
        public readonly ?string $name,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        if (!array_key_exists('chatId', $data) || !is_string($data['chatId'])) {
            throw new ValidationError(message: "Missing or non-string field 'chatId' in GroupParticipant response");
        }
        if (!array_key_exists('isAdmin', $data) || !is_bool($data['isAdmin'])) {
            throw new ValidationError(message: "Missing or non-bool field 'isAdmin' in GroupParticipant response");
        }
        if (!array_key_exists('isSuperAdmin', $data) || !is_bool($data['isSuperAdmin'])) {
            throw new ValidationError(
                message: "Missing or non-bool field 'isSuperAdmin' in GroupParticipant response",
            );
        }
        if (array_key_exists('name', $data) && $data['name'] !== null && !is_string($data['name'])) {
            throw new ValidationError(message: "Field 'name' must be string or null in GroupParticipant response");
        }

        return new self(
            chatId: $data['chatId'],
            isAdmin: $data['isAdmin'],
            isSuperAdmin: $data['isSuperAdmin'],
            name: $data['name'] ?? null,
        );
    }
}
