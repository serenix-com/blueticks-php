<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * A call log entry (`type: call_log` messages only).
 */
final class PublicCall
{
    public const DIRECTIONS = ['in', 'out'];

    public const MEDIA = ['voice', 'video'];

    public const OUTCOMES = ['answered', 'missed', 'rejected', 'unknown'];

    public function __construct(
        public readonly string $direction,
        public readonly string $media,
        public readonly string $outcome,
        public readonly bool $isGroup,
        public readonly ?int $durationSec = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        self::assertEnum($data, 'direction', self::DIRECTIONS);
        self::assertEnum($data, 'media', self::MEDIA);
        self::assertEnum($data, 'outcome', self::OUTCOMES);
        if (!array_key_exists('isGroup', $data) || !is_bool($data['isGroup'])) {
            throw new ValidationError(message: "Missing or non-bool field 'isGroup' in PublicCall response");
        }
        if (array_key_exists('durationSec', $data) && $data['durationSec'] !== null && !is_int($data['durationSec'])) {
            throw new ValidationError(message: "Field 'durationSec' must be integer or null in PublicCall response");
        }

        return new self(
            direction: $data['direction'],
            media: $data['media'],
            outcome: $data['outcome'],
            isGroup: $data['isGroup'],
            durationSec: isset($data['durationSec']) && is_int($data['durationSec']) ? $data['durationSec'] : null,
        );
    }

    /**
     * @param array<string, mixed> $data
     * @param list<string>         $allowed
     */
    private static function assertEnum(array $data, string $key, array $allowed): void
    {
        if (!array_key_exists($key, $data) || !is_string($data[$key]) || !in_array($data[$key], $allowed, true)) {
            throw new ValidationError(message: "Missing or unknown field '{$key}' in PublicCall response");
        }
    }
}
