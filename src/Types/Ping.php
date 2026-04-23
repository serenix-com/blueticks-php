<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

final readonly class Ping
{
    /**
     * @param list<string> $scopes
     */
    public function __construct(
        public string $account_id,
        public string $key_prefix,
        public array $scopes,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        self::assertString($data, 'account_id');
        self::assertString($data, 'key_prefix');
        self::assertStringList($data, 'scopes');

        /** @var list<string> $scopes */
        $scopes = array_values($data['scopes']);

        return new self(
            account_id: $data['account_id'],
            key_prefix: $data['key_prefix'],
            scopes: $scopes,
        );
    }

    /** @param array<string, mixed> $data */
    private static function assertString(array $data, string $key): void
    {
        if (!array_key_exists($key, $data) || !is_string($data[$key])) {
            throw new ValidationError(message: "Missing or non-string field '{$key}' in Ping response");
        }
    }

    /** @param array<string, mixed> $data */
    private static function assertStringList(array $data, string $key): void
    {
        if (!array_key_exists($key, $data) || !is_array($data[$key])) {
            throw new ValidationError(message: "Missing or non-array field '{$key}' in Ping response");
        }
        foreach ($data[$key] as $item) {
            if (!is_string($item)) {
                throw new ValidationError(message: "Field '{$key}' in Ping response must contain only strings");
            }
        }
    }
}
