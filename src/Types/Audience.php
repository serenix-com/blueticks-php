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
        if (!array_key_exists('contact_count', $raw) || !is_int($raw['contact_count'])) {
            throw new ValidationError(message: "Missing or non-int field 'contact_count' in Audience response");
        }
        self::assertString($raw, 'created_at');

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
        if (array_key_exists('has_more', $raw) && $raw['has_more'] !== null) {
            if (!is_bool($raw['has_more'])) {
                throw new ValidationError(message: "Field 'has_more' must be bool or null in Audience response");
            }
            $hasMore = $raw['has_more'];
        }

        return new self(
            id: $raw['id'],
            name: $raw['name'],
            contactCount: $raw['contact_count'],
            createdAt: $raw['created_at'],
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
