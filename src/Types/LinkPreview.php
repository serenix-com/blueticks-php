<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * Link-preview metadata attached to a message that carried a URL.
 */
final class LinkPreview
{
    public function __construct(
        public readonly ?string $title,
        public readonly ?string $description,
        public readonly ?string $canonicalUrl,
        public readonly ?string $thumbnail,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        self::assertStringOrNull($data, 'title');
        self::assertStringOrNull($data, 'description');
        self::assertStringOrNull($data, 'canonicalUrl');
        self::assertStringOrNull($data, 'thumbnail');

        return new self(
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
            canonicalUrl: $data['canonicalUrl'] ?? null,
            thumbnail: $data['thumbnail'] ?? null,
        );
    }

    /** @param array<string, mixed> $data */
    private static function assertStringOrNull(array $data, string $key): void
    {
        if (array_key_exists($key, $data) && $data[$key] !== null && !is_string($data[$key])) {
            throw new ValidationError(message: "Field '{$key}' must be string or null in LinkPreview response");
        }
    }
}
