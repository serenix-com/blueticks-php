<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * The repeat rule of a recurring scheduled message. Absent for one-time
 * messages (the owning `Message::$recurrence` is then null).
 */
final class RecurrenceResponse
{
    public const FREQUENCIES = ['daily', 'weekly', 'monthly', 'yearly'];

    public const WEEKDAYS = ['MO', 'TU', 'WE', 'TH', 'FR', 'SA', 'SU'];

    /**
     * @param ?list<string> $byWeekday
     */
    public function __construct(
        public readonly string $frequency,
        public readonly int $interval,
        public readonly ?array $byWeekday,
        public readonly ?int $endsAfter,
        public readonly ?string $endsOn,
        public readonly ?string $timezone,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        self::assertEnum($data, 'frequency', self::FREQUENCIES);
        if (!array_key_exists('interval', $data) || !is_int($data['interval'])) {
            throw new ValidationError(
                message: "Missing or non-integer field 'interval' in RecurrenceResponse response",
            );
        }
        $byWeekday = self::byWeekday($data);
        if (array_key_exists('endsAfter', $data) && $data['endsAfter'] !== null && !is_int($data['endsAfter'])) {
            throw new ValidationError(
                message: "Field 'endsAfter' must be integer or null in RecurrenceResponse response",
            );
        }
        if (array_key_exists('endsOn', $data) && $data['endsOn'] !== null && !is_string($data['endsOn'])) {
            throw new ValidationError(message: "Field 'endsOn' must be string or null in RecurrenceResponse response");
        }
        if (array_key_exists('timezone', $data) && $data['timezone'] !== null && !is_string($data['timezone'])) {
            throw new ValidationError(
                message: "Field 'timezone' must be string or null in RecurrenceResponse response",
            );
        }

        return new self(
            frequency: $data['frequency'],
            interval: $data['interval'],
            byWeekday: $byWeekday,
            endsAfter: isset($data['endsAfter']) && is_int($data['endsAfter']) ? $data['endsAfter'] : null,
            endsOn: isset($data['endsOn']) && is_string($data['endsOn']) ? $data['endsOn'] : null,
            timezone: isset($data['timezone']) && is_string($data['timezone']) ? $data['timezone'] : null,
        );
    }

    /**
     * @param array<string, mixed> $data
     * @return ?list<string>
     */
    private static function byWeekday(array $data): ?array
    {
        if (!array_key_exists('byWeekday', $data) || $data['byWeekday'] === null) {
            return null;
        }
        if (!is_array($data['byWeekday'])) {
            throw new ValidationError(
                message: "Field 'byWeekday' must be array or null in RecurrenceResponse response",
            );
        }
        $out = [];
        foreach ($data['byWeekday'] as $day) {
            if (!is_string($day) || !in_array($day, self::WEEKDAYS, true)) {
                throw new ValidationError(
                    message: "Field 'byWeekday' has an unknown weekday in RecurrenceResponse response",
                );
            }
            $out[] = $day;
        }
        return $out;
    }

    /**
     * @param array<string, mixed> $data
     * @param list<string>         $allowed
     */
    private static function assertEnum(array $data, string $key, array $allowed): void
    {
        if (!array_key_exists($key, $data) || !is_string($data[$key]) || !in_array($data[$key], $allowed, true)) {
            throw new ValidationError(message: "Missing or unknown field '{$key}' in RecurrenceResponse response");
        }
    }
}
