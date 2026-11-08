<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * One reaction on a message. `senderId` is best-effort; `fromMe` is true
 * when the connected account is the reactor.
 */
final class PublicReaction
{
    public function __construct(
        public readonly string $emoji,
        public readonly ?string $senderId = null,
        public readonly ?bool $fromMe = null,
        public readonly ?string $timestamp = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        if (!array_key_exists('emoji', $data) || !is_string($data['emoji'])) {
            throw new ValidationError(message: "Missing or non-string field 'emoji' in PublicReaction response");
        }
        foreach (['senderId', 'timestamp'] as $key) {
            if (array_key_exists($key, $data) && $data[$key] !== null && !is_string($data[$key])) {
                throw new ValidationError(message: "Field '{$key}' must be string or null in PublicReaction response");
            }
        }
        if (array_key_exists('fromMe', $data) && $data['fromMe'] !== null && !is_bool($data['fromMe'])) {
            throw new ValidationError(message: "Field 'fromMe' must be bool or null in PublicReaction response");
        }

        return new self(
            emoji: $data['emoji'],
            senderId: isset($data['senderId']) && is_string($data['senderId']) ? $data['senderId'] : null,
            fromMe: isset($data['fromMe']) && is_bool($data['fromMe']) ? $data['fromMe'] : null,
            timestamp: isset($data['timestamp']) && is_string($data['timestamp']) ? $data['timestamp'] : null,
        );
    }
}
