<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

final class GroupParticipant
{
    public function __construct(
        public readonly string $chat_id,
        public readonly bool $is_admin,
        public readonly bool $is_super_admin,
        public readonly ?string $name,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        self::assertString($data, 'chat_id');
        self::assertBool($data, 'is_admin');
        self::assertBool($data, 'is_super_admin');
        self::assertStringOrNull($data, 'name');

        /** @var string $chatId */
        $chatId = $data['chat_id'];
        /** @var bool $isAdmin */
        $isAdmin = $data['is_admin'];
        /** @var bool $isSuperAdmin */
        $isSuperAdmin = $data['is_super_admin'];
        /** @var ?string $name */
        $name = $data['name'];

        return new self(
            chat_id: $chatId,
            is_admin: $isAdmin,
            is_super_admin: $isSuperAdmin,
            name: $name,
        );
    }

    /** @param array<string, mixed> $data */
    private static function assertString(array $data, string $key): void
    {
        if (!array_key_exists($key, $data) || !is_string($data[$key])) {
            throw new ValidationError(message: "Missing or non-string field '{$key}' in GroupParticipant response");
        }
    }

    /** @param array<string, mixed> $data */
    private static function assertBool(array $data, string $key): void
    {
        if (!array_key_exists($key, $data) || !is_bool($data[$key])) {
            throw new ValidationError(message: "Missing or non-bool field '{$key}' in GroupParticipant response");
        }
    }

    /** @param array<string, mixed> $data */
    private static function assertStringOrNull(array $data, string $key): void
    {
        if (!array_key_exists($key, $data)) {
            throw new ValidationError(message: "Missing field '{$key}' in GroupParticipant response");
        }
        if ($data[$key] !== null && !is_string($data[$key])) {
            throw new ValidationError(message: "Field '{$key}' must be string or null in GroupParticipant response");
        }
    }
}
