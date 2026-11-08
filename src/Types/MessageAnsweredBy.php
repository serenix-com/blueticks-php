<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * Which engine answered a message read, and the account number it is on.
 */
final class MessageAnsweredBy
{
    public function __construct(
        public readonly string $engineId,
        public readonly ?string $numberId = null,
        public readonly ?string $phoneNumber = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        if (!array_key_exists('engineId', $data) || !is_string($data['engineId'])) {
            throw new ValidationError(message: "Missing or non-string field 'engineId' in MessageAnsweredBy response");
        }
        foreach (['numberId', 'phoneNumber'] as $key) {
            if (array_key_exists($key, $data) && $data[$key] !== null && !is_string($data[$key])) {
                throw new ValidationError(
                    message: "Field '{$key}' must be string or null in MessageAnsweredBy response",
                );
            }
        }

        return new self(
            engineId: $data['engineId'],
            numberId: isset($data['numberId']) && is_string($data['numberId']) ? $data['numberId'] : null,
            phoneNumber: isset($data['phoneNumber']) && is_string($data['phoneNumber']) ? $data['phoneNumber'] : null,
        );
    }
}
