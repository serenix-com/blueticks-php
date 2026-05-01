<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * Single-message delivery status returned by
 * `GET /v1/chats/{chat_id}/messages/{key}/ack`.
 *
 * `ack` semantics: -1=error, 0=pending, 1=server, 2=device, 3=read,
 * 4=played; null when no engine response.
 */
final class MessageAck
{
    public function __construct(
        public readonly ?int $ack,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        if (!array_key_exists('ack', $data)) {
            throw new ValidationError(message: "Missing field 'ack' in MessageAck response");
        }
        if ($data['ack'] !== null && !is_int($data['ack'])) {
            throw new ValidationError(message: "Field 'ack' must be int or null in MessageAck response");
        }

        /** @var ?int $ack */
        $ack = $data['ack'];

        return new self(ack: $ack);
    }
}
