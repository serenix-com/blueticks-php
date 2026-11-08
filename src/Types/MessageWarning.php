<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * A non-fatal warning attached to a scheduled message.
 * `no_engine_connected`: no WhatsApp engine was connected when the message
 * was scheduled.
 */
final class MessageWarning
{
    public const CODES = ['no_engine_connected'];

    public function __construct(
        public readonly string $code,
        public readonly string $message,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        if (
            !array_key_exists('code', $data)
            || !is_string($data['code'])
            || !in_array($data['code'], self::CODES, true)
        ) {
            throw new ValidationError(message: "Missing or unknown field 'code' in MessageWarning response");
        }
        if (!array_key_exists('message', $data) || !is_string($data['message'])) {
            throw new ValidationError(message: "Missing or non-string field 'message' in MessageWarning response");
        }

        return new self(code: $data['code'], message: $data['message']);
    }
}
