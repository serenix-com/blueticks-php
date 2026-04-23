<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

final class Contact
{
    /**
     * @param array<string, string> $variables
     */
    public function __construct(
        public readonly string $id,
        public readonly string $to,
        public readonly array $variables,
        public readonly string $addedAt,
    ) {
    }

    /**
     * @param array<string, mixed> $raw
     */
    public static function fromArray(array $raw): self
    {
        self::assertString($raw, 'id');
        self::assertString($raw, 'to');
        self::assertString($raw, 'added_at');
        if (!array_key_exists('variables', $raw) || !is_array($raw['variables'])) {
            throw new ValidationError(message: "Missing or non-array field 'variables' in Contact response");
        }
        $variables = [];
        foreach ($raw['variables'] as $k => $v) {
            if (!is_string($k) || !is_string($v)) {
                throw new ValidationError(message: "Field 'variables' in Contact response must map strings to strings");
            }
            $variables[$k] = $v;
        }

        return new self(
            id: $raw['id'],
            to: $raw['to'],
            variables: $variables,
            addedAt: $raw['added_at'],
        );
    }

    /** @param array<string, mixed> $data */
    private static function assertString(array $data, string $key): void
    {
        if (!array_key_exists($key, $data) || !is_string($data[$key])) {
            throw new ValidationError(message: "Missing or non-string field '{$key}' in Contact response");
        }
    }
}
