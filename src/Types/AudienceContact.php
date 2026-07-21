<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * A single contact belonging to an audience, as returned by
 * `PATCH /v1/audiences/{id}/contacts/{contactId}`.
 */
final class AudienceContact
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
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        self::assertString($data, 'id');
        self::assertString($data, 'to');
        self::assertString($data, 'addedAt');
        if (!array_key_exists('variables', $data) || !is_array($data['variables'])) {
            throw new ValidationError(message: "Missing or non-array field 'variables' in AudienceContact response");
        }
        $variables = [];
        foreach ($data['variables'] as $k => $v) {
            if (!is_string($k) || !is_string($v)) {
                throw new ValidationError(
                    message: "Field 'variables' in AudienceContact response must map strings to strings",
                );
            }
            $variables[$k] = $v;
        }

        return new self(
            id: $data['id'],
            to: $data['to'],
            variables: $variables,
            addedAt: $data['addedAt'],
        );
    }

    /** @param array<string, mixed> $data */
    private static function assertString(array $data, string $key): void
    {
        if (!array_key_exists($key, $data) || !is_string($data[$key])) {
            throw new ValidationError(message: "Missing or non-string field '{$key}' in AudienceContact response");
        }
    }
}
