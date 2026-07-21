<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * A WhatsApp contact from the connected account's address book,
 * as returned by `GET /v1/contacts`.
 */
final class Contact
{
    public function __construct(
        public readonly string $chatId,
        public readonly ?string $name,
        public readonly ?bool $isBusiness,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        if (!array_key_exists('chatId', $data) || !is_string($data['chatId'])) {
            throw new ValidationError(message: "Missing or non-string field 'chatId' in Contact response");
        }
        if (array_key_exists('name', $data) && $data['name'] !== null && !is_string($data['name'])) {
            throw new ValidationError(message: "Field 'name' must be string or null in Contact response");
        }
        if (
            array_key_exists('isBusiness', $data)
            && $data['isBusiness'] !== null
            && !is_bool($data['isBusiness'])
        ) {
            throw new ValidationError(message: "Field 'isBusiness' must be bool or null in Contact response");
        }

        return new self(
            chatId: $data['chatId'],
            name: $data['name'] ?? null,
            isBusiness: $data['isBusiness'] ?? null,
        );
    }
}
