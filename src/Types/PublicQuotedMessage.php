<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * The message quoted by a {@see PublicMessage} (the "reply-to" target).
 */
final class PublicQuotedMessage
{
    public function __construct(
        public readonly WaMessageKey $waMessageKey,
        public readonly ?string $text,
        public readonly ?string $type,
        public readonly ?string $author,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        if (!array_key_exists('waMessageKey', $data) || !is_array($data['waMessageKey'])) {
            throw new ValidationError(
                message: "Missing or non-object field 'waMessageKey' in PublicQuotedMessage response",
            );
        }
        /** @var array<string, mixed> $keyData */
        $keyData = $data['waMessageKey'];
        self::assertStringOrNull($data, 'text');
        self::assertStringOrNull($data, 'type');
        self::assertStringOrNull($data, 'author');

        return new self(
            waMessageKey: WaMessageKey::fromArray($keyData),
            text: $data['text'] ?? null,
            type: $data['type'] ?? null,
            author: $data['author'] ?? null,
        );
    }

    /** @param array<string, mixed> $data */
    private static function assertStringOrNull(array $data, string $key): void
    {
        if (array_key_exists($key, $data) && $data[$key] !== null && !is_string($data[$key])) {
            throw new ValidationError(
                message: "Field '{$key}' must be string or null in PublicQuotedMessage response",
            );
        }
    }
}
