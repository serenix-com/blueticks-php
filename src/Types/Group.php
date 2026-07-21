<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * A WhatsApp group. List endpoints (`GET /v1/groups`,
 * `GET /v1/contacts/{id}/common_groups`) return the lighter list-item
 * subset (no `announce`/`participants`); single-group endpoints return
 * the full snapshot. Every field except `id` may be absent.
 */
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
        public readonly ?string $createdAt,
        public readonly ?string $lastMessageAt,
        public readonly ?int $participantCount,
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
        if (!array_key_exists('id', $data) || !is_string($data['id'])) {
            throw new ValidationError(message: "Missing or non-string field 'id' in Group response");
        }
        self::assertStringOrNull($data, 'name');
        self::assertStringOrNull($data, 'description');
        self::assertStringOrNull($data, 'owner');
        self::assertStringOrNull($data, 'createdAt');
        self::assertStringOrNull($data, 'lastMessageAt');
        self::assertIntOrNull($data, 'participantCount');
        self::assertBoolOrNull($data, 'announce');
        self::assertBoolOrNull($data, 'restrict');

        $participants = null;
        if (array_key_exists('participants', $data) && $data['participants'] !== null) {
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

        return new self(
            id: $data['id'],
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
            owner: $data['owner'] ?? null,
            createdAt: $data['createdAt'] ?? null,
            lastMessageAt: $data['lastMessageAt'] ?? null,
            participantCount: $data['participantCount'] ?? null,
            announce: $data['announce'] ?? null,
            restrict: $data['restrict'] ?? null,
            participants: $participants,
        );
    }

    /** @param array<string, mixed> $data */
    private static function assertStringOrNull(array $data, string $key): void
    {
        if (array_key_exists($key, $data) && $data[$key] !== null && !is_string($data[$key])) {
            throw new ValidationError(message: "Field '{$key}' must be string or null in Group response");
        }
    }

    /** @param array<string, mixed> $data */
    private static function assertIntOrNull(array $data, string $key): void
    {
        if (array_key_exists($key, $data) && $data[$key] !== null && !is_int($data[$key])) {
            throw new ValidationError(message: "Field '{$key}' must be int or null in Group response");
        }
    }

    /** @param array<string, mixed> $data */
    private static function assertBoolOrNull(array $data, string $key): void
    {
        if (array_key_exists($key, $data) && $data[$key] !== null && !is_bool($data[$key])) {
            throw new ValidationError(message: "Field '{$key}' must be bool or null in Group response");
        }
    }
}
