<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * How much of the store a `GET /v1/messages` read covered. Every field is
 * optional; see the spec for the meaning of each.
 */
final class MessageListCoverage
{
    public function __construct(
        public readonly ?float $oldestLoadedTimestamp = null,
        public readonly ?string $oldestLoadedIso = null,
        public readonly ?int $messagesScanned = null,
        public readonly ?bool $truncated = null,
        public readonly ?bool $indexOnly = null,
        public readonly ?string $oldestScannedIso = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $ts = null;
        if (array_key_exists('oldestLoadedTimestamp', $data) && $data['oldestLoadedTimestamp'] !== null) {
            if (!is_int($data['oldestLoadedTimestamp']) && !is_float($data['oldestLoadedTimestamp'])) {
                throw new ValidationError(
                    message: "Field 'oldestLoadedTimestamp' must be numeric in MessageListCoverage response",
                );
            }
            $ts = (float) $data['oldestLoadedTimestamp'];
        }
        self::assertOptional($data, 'oldestLoadedIso', 'string');
        self::assertOptional($data, 'messagesScanned', 'int');
        self::assertOptional($data, 'truncated', 'bool');
        self::assertOptional($data, 'indexOnly', 'bool');
        self::assertOptional($data, 'oldestScannedIso', 'string');

        return new self(
            oldestLoadedTimestamp: $ts,
            oldestLoadedIso: isset($data['oldestLoadedIso']) && is_string($data['oldestLoadedIso'])
                ? $data['oldestLoadedIso']
                : null,
            messagesScanned: isset($data['messagesScanned']) && is_int($data['messagesScanned'])
                ? $data['messagesScanned']
                : null,
            truncated: isset($data['truncated']) && is_bool($data['truncated']) ? $data['truncated'] : null,
            indexOnly: isset($data['indexOnly']) && is_bool($data['indexOnly']) ? $data['indexOnly'] : null,
            oldestScannedIso: isset($data['oldestScannedIso']) && is_string($data['oldestScannedIso'])
                ? $data['oldestScannedIso']
                : null,
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function assertOptional(array $data, string $key, string $type): void
    {
        if (!array_key_exists($key, $data) || $data[$key] === null) {
            return;
        }
        $ok = match ($type) {
            'string' => is_string($data[$key]),
            'int' => is_int($data[$key]),
            default => is_bool($data[$key]),
        };
        if (!$ok) {
            throw new ValidationError(
                message: "Field '{$key}' must be {$type} or null in MessageListCoverage response",
            );
        }
    }
}
