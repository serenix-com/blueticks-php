<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * Response payload for
 * `GET /v1/chats/{chat_id}/messages/{key}/media_url`. The URL is null
 * when the engine could not produce a hosted URL for the bytes.
 */
final class MediaUrlResponse
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
            throw new ValidationError(message: "Missing field 'url' in MediaUrlResponse response");
        }
        if ($data['url'] !== null && !is_string($data['url'])) {
            throw new ValidationError(message: "Field 'url' must be string or null in MediaUrlResponse response");
        }

        /** @var ?string $url */
        $url = $data['url'];

        return new self(url: $url);
    }
}
