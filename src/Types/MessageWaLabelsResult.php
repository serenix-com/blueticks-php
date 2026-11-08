<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * Outcome of applying native WhatsApp labels to a scheduled or sent
 * message (`Message::$waLabels`). `error` is set when WhatsApp did not
 * apply them, e.g. `wa_business_required`.
 */
final class MessageWaLabelsResult
{
    public function __construct(
        public readonly int $requested,
        public readonly bool $applied,
        public readonly ?string $error = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        if (!array_key_exists('requested', $data) || !is_int($data['requested'])) {
            throw new ValidationError(
                message: "Missing or non-integer field 'requested' in MessageWaLabelsResult response",
            );
        }
        if (!array_key_exists('applied', $data) || !is_bool($data['applied'])) {
            throw new ValidationError(message: "Missing or non-bool field 'applied' in MessageWaLabelsResult response");
        }
        if (array_key_exists('error', $data) && $data['error'] !== null && !is_string($data['error'])) {
            throw new ValidationError(
                message: "Field 'error' must be string or null in MessageWaLabelsResult response",
            );
        }

        return new self(
            requested: $data['requested'],
            applied: $data['applied'],
            error: isset($data['error']) && is_string($data['error']) ? $data['error'] : null,
        );
    }
}
