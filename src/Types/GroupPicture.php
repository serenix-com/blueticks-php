<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * Current group picture. `url` is a short-lived WhatsApp CDN URL, or null
 * when the group has no picture.
 */
final class GroupPicture
{
    public function __construct(
        public readonly ?string $url,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        if (!array_key_exists('url', $data)) {
            throw new ValidationError(message: "Missing field 'url' in GroupPicture response");
        }
        if ($data['url'] !== null && !is_string($data['url'])) {
            throw new ValidationError(message: "Field 'url' must be string or null in GroupPicture response");
        }

        return new self(url: $data['url']);
    }
}
