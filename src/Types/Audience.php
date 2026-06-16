<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

final class Audience
{
    /**
     * @param ?list<Contact> $contacts
     */
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly int $contactCount,
        public readonly string $createdAt,
        public readonly ?array $contacts,
        public readonly ?int $page,
        public readonly ?bool $hasMore,
    ) {
    }

    /**
     * @param array<string, mixed> $raw
     */
    public static function fromArray(array $raw): self
    {
        self::assertString($raw, 'id');
        self::assertString($raw, 'name');
        if (!array_key_exists('contactCount', $raw) || !is_int($raw['contactCount'])) {
            throw new ValidationError(message: "Missing or non-int field 'contactCount' in Audience response");
        }
        self::assertString($raw, 'createdAt');

        $contacts = null;
        if (array_key_exists('contacts', $raw)) {
            if (!is_array($raw['contacts'])) {
                throw new ValidationError(message: "Field 'contacts' must be an array in Audience response");
            }
            $contacts = [];
            foreach ($raw['contacts'] as $c) {
                if (!is_array($c)) {
                    throw new ValidationError(message: "Field 'contacts' must contain objects in Audience response");
                }
                /** @var array<string, mixed> $c */
                $contacts[] = Contact::fromArray($c);
            }
        }

        $page = null;
        if (array_key_exists('page', $raw) && $raw['page'] !== null) {
            if (!is_int($raw['page'])) {
                throw new ValidationError(message: "Field 'page' must be int or null in Audience response");
            }
            $page = $raw['page'];
        }

        $hasMore = null;
        if (array_key_exists('hasMore', $raw) && $raw['hasMore'] !== null) {
            if (!is_bool($raw['hasMore'])) {
                throw new ValidationError(message: "Field 'hasMore' must be bool or null in Audience response");
            }
            $hasMore = $raw['hasMore'];
        }

        return new self(
            id: $raw['id'],
            name: $raw['name'],
            contactCount: $raw['contactCount'],
            createdAt: $raw['createdAt'],
            contacts: $contacts,
            page: $page,
            hasMore: $hasMore,
        );
    }

    /** @param array<string, mixed> $data */
    private static function assertString(array $data, string $key): void
    {
        if (!array_key_exists($key, $data) || !is_string($data[$key])) {
            throw new ValidationError(message: "Missing or non-string field '{$key}' in Audience response");
        }
    }
}
