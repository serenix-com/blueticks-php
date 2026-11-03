<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * Result of asking the engine to pull older history from the phone,
 * returned by `POST /v1/messages/load_older/{chatId}`.
 */
final class LoadOlderResult
{
    public function __construct(
        public readonly ?int $totalMessages,
        public readonly ?int $added,
        public readonly bool $canLoadMore,
        public readonly bool $historyUnavailable,
        public readonly ?string $error,
        public readonly int $pagesExecuted,
        public readonly LoadCoverage $coverage,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        self::assertIntOrNull($data, 'totalMessages');
        self::assertIntOrNull($data, 'added');
        if (!array_key_exists('canLoadMore', $data) || !is_bool($data['canLoadMore'])) {
            throw new ValidationError(message: "Missing or non-bool field 'canLoadMore' in LoadOlderResult response");
        }
        if (!array_key_exists('historyUnavailable', $data) || !is_bool($data['historyUnavailable'])) {
            throw new ValidationError(
                message: "Missing or non-bool field 'historyUnavailable' in LoadOlderResult response",
            );
        }
        if (array_key_exists('error', $data) && $data['error'] !== null && !is_string($data['error'])) {
            throw new ValidationError(message: "Field 'error' must be string or null in LoadOlderResult response");
        }
        if (!array_key_exists('pagesExecuted', $data) || !is_int($data['pagesExecuted'])) {
            throw new ValidationError(
                message: "Missing or non-int field 'pagesExecuted' in LoadOlderResult response",
            );
        }
        if (!array_key_exists('coverage', $data) || !is_array($data['coverage'])) {
            throw new ValidationError(
                message: "Missing or non-object field 'coverage' in LoadOlderResult response",
            );
        }
        /** @var array<string, mixed> $coverage */
        $coverage = $data['coverage'];

        return new self(
            totalMessages: $data['totalMessages'] ?? null,
            added: $data['added'] ?? null,
            canLoadMore: $data['canLoadMore'],
            historyUnavailable: $data['historyUnavailable'],
            error: $data['error'] ?? null,
            pagesExecuted: $data['pagesExecuted'],
            coverage: LoadCoverage::fromArray($coverage),
        );
    }

    /** @param array<string, mixed> $data */
    private static function assertIntOrNull(array $data, string $key): void
    {
        if (array_key_exists($key, $data) && $data[$key] !== null && !is_int($data[$key])) {
            throw new ValidationError(message: "Field '{$key}' must be int or null in LoadOlderResult response");
        }
    }
}
