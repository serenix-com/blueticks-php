<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * A newsletter row as returned by `GET /v1/newsletters`. Identical to
 * {@see Newsletter} except the identity field is keyed `chatId` (matching
 * how the chats/contacts/groups list endpoints key rows).
 */
final class NewsletterListItem
{
    public function __construct(
        public readonly string $chatId,
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?string $createdAt,
        public readonly ?int $subscribers,
        public readonly ?string $invite,
        public readonly ?string $verification,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        self::assertString($data, 'chatId');
        self::assertString($data, 'name');
        self::assertStringOrNull($data, 'description');
        self::assertStringOrNull($data, 'createdAt');
        self::assertIntOrNull($data, 'subscribers');
        self::assertStringOrNull($data, 'invite');
        self::assertVerificationOrNull($data, 'verification');

        return new self(
            chatId: $data['chatId'],
            name: $data['name'],
            description: $data['description'],
            createdAt: $data['createdAt'],
            subscribers: $data['subscribers'],
            invite: $data['invite'],
            verification: $data['verification'],
        );
    }

    /** @param array<string, mixed> $data */
    private static function assertString(array $data, string $key): void
    {
        if (!array_key_exists($key, $data) || !is_string($data[$key])) {
            throw new ValidationError(message: "Missing or non-string field '{$key}' in NewsletterListItem response");
        }
    }

    /** @param array<string, mixed> $data */
    private static function assertStringOrNull(array $data, string $key): void
    {
        if (!array_key_exists($key, $data)) {
            throw new ValidationError(message: "Missing field '{$key}' in NewsletterListItem response");
        }
        if ($data[$key] !== null && !is_string($data[$key])) {
            throw new ValidationError(message: "Field '{$key}' must be string or null in NewsletterListItem response");
        }
    }

    /** @param array<string, mixed> $data */
    private static function assertIntOrNull(array $data, string $key): void
    {
        if (!array_key_exists($key, $data)) {
            throw new ValidationError(message: "Missing field '{$key}' in NewsletterListItem response");
        }
        if ($data[$key] !== null && !is_int($data[$key])) {
            throw new ValidationError(message: "Field '{$key}' must be integer or null in NewsletterListItem response");
        }
    }

    /** @param array<string, mixed> $data */
    private static function assertVerificationOrNull(array $data, string $key): void
    {
        if (!array_key_exists($key, $data)) {
            throw new ValidationError(message: "Missing field '{$key}' in NewsletterListItem response");
        }
        if ($data[$key] !== null) {
            if (!is_string($data[$key]) || !in_array($data[$key], ['VERIFIED', 'UNVERIFIED'], true)) {
                throw new ValidationError(message: "Field '{$key}' must be 'VERIFIED', 'UNVERIFIED', or null in NewsletterListItem response");
            }
        }
    }
}
