<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

final class Group
{
    /**
     * @param ?list<GroupParticipant> $participants
     */
    public function __construct(
        public readonly string $id,
        public readonly ?string $name,
        public readonly ?string $description,
        public readonly ?string $owner,
        public readonly ?string $created_at,
        public readonly ?string $last_message_at,
        public readonly ?int $participant_count,
        public readonly ?bool $announce,
        public readonly ?bool $restrict,
        public readonly ?array $participants,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        self::assertString($data, 'id');
        self::assertStringOrNull($data, 'name');
        self::assertStringOrNull($data, 'description');
        self::assertStringOrNull($data, 'owner');
        self::assertStringOrNull($data, 'created_at');
        self::assertStringOrNull($data, 'last_message_at');
        self::assertIntOrNull($data, 'participant_count');
        self::assertBoolOrNull($data, 'announce');
        self::assertBoolOrNull($data, 'restrict');

        if (!array_key_exists('participants', $data)) {
            throw new ValidationError(message: "Missing field 'participants' in Group response");
        }
        $participants = null;
        if ($data['participants'] !== null) {
            if (!is_array($data['participants'])) {
                throw new ValidationError(message: "Field 'participants' must be array or null in Group response");
            }
            $participants = [];
            foreach ($data['participants'] as $row) {
                if (!is_array($row)) {
                    throw new ValidationError(
                        message: "Each entry of 'participants' must be an object in Group response",
                    );
                }
                /** @var array<string, mixed> $row */
                $participants[] = GroupParticipant::fromArray($row);
            }
        }

        /** @var string $id */
        $id = $data['id'];
        /** @var ?string $name */
        $name = $data['name'];
        /** @var ?string $description */
        $description = $data['description'];
        /** @var ?string $owner */
        $owner = $data['owner'];
        /** @var ?string $createdAt */
        $createdAt = $data['created_at'];
        /** @var ?string $lastMessageAt */
        $lastMessageAt = $data['last_message_at'];
        /** @var ?int $participantCount */
        $participantCount = $data['participant_count'];
        /** @var ?bool $announce */
        $announce = $data['announce'];
        /** @var ?bool $restrict */
        $restrict = $data['restrict'];

        return new self(
            id: $id,
            name: $name,
            description: $description,
            owner: $owner,
            created_at: $createdAt,
            last_message_at: $lastMessageAt,
            participant_count: $participantCount,
            announce: $announce,
            restrict: $restrict,
            participants: $participants,
        );
    }

    /** @param array<string, mixed> $data */
    private static function assertString(array $data, string $key): void
    {
        if (!array_key_exists($key, $data) || !is_string($data[$key])) {
            throw new ValidationError(message: "Missing or non-string field '{$key}' in Group response");
        }
    }

    /** @param array<string, mixed> $data */
    private static function assertStringOrNull(array $data, string $key): void
    {
        if (!array_key_exists($key, $data)) {
            throw new ValidationError(message: "Missing field '{$key}' in Group response");
        }
        if ($data[$key] !== null && !is_string($data[$key])) {
            throw new ValidationError(message: "Field '{$key}' must be string or null in Group response");
        }
    }

    /** @param array<string, mixed> $data */
    private static function assertIntOrNull(array $data, string $key): void
    {
        if (!array_key_exists($key, $data)) {
            throw new ValidationError(message: "Missing field '{$key}' in Group response");
        }
        if ($data[$key] !== null && !is_int($data[$key])) {
            throw new ValidationError(message: "Field '{$key}' must be int or null in Group response");
        }
    }

    /** @param array<string, mixed> $data */
    private static function assertBoolOrNull(array $data, string $key): void
    {
        if (!array_key_exists($key, $data)) {
            throw new ValidationError(message: "Missing field '{$key}' in Group response");
        }
        if ($data[$key] !== null && !is_bool($data[$key])) {
            throw new ValidationError(message: "Field '{$key}' must be bool or null in Group response");
        }
    }
}
