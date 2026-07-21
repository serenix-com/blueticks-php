<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * One member of a WhatsApp group (or the single counterparty of a DM),
 * as returned by `GET /v1/chats/{chatId}/participants`.
 */
final class Participant
{
    public function __construct(
        public readonly string $chatId,
        public readonly ?string $name,
        public readonly bool $isAdmin,
        public readonly ?bool $isSuperAdmin,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        if (!array_key_exists('chatId', $data) || !is_string($data['chatId'])) {
            throw new ValidationError(message: "Missing or non-string field 'chatId' in Participant response");
        }
        if (array_key_exists('name', $data) && $data['name'] !== null && !is_string($data['name'])) {
            throw new ValidationError(message: "Field 'name' must be string or null in Participant response");
        }
        if (!array_key_exists('isAdmin', $data) || !is_bool($data['isAdmin'])) {
            throw new ValidationError(message: "Missing or non-bool field 'isAdmin' in Participant response");
        }
        if (
            array_key_exists('isSuperAdmin', $data)
            && $data['isSuperAdmin'] !== null
            && !is_bool($data['isSuperAdmin'])
        ) {
            throw new ValidationError(message: "Field 'isSuperAdmin' must be bool or null in Participant response");
        }

        return new self(
            chatId: $data['chatId'],
            name: $data['name'] ?? null,
            isAdmin: $data['isAdmin'],
            isSuperAdmin: $data['isSuperAdmin'] ?? null,
        );
    }
}
