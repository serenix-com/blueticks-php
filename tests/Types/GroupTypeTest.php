<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\Group;
use Blueticks\Types\GroupParticipant;
use PHPUnit\Framework\TestCase;

final class GroupTypeTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function fixture(): array
    {
        return [
            'id' => '120363021000000000@g.us',
            'name' => 'Team',
            'description' => 'Internal team chat',
            'owner' => '15551234@c.us',
            'createdAt' => '2026-04-23T10:00:00Z',
            'lastMessageAt' => '2026-05-01T08:30:00Z',
            'participantCount' => 2,
            'announce' => false,
            'restrict' => true,
            'participants' => [
                [
                    'chatId' => '15551234@c.us',
                    'isAdmin' => true,
                    'isSuperAdmin' => true,
                    'name' => 'Alice',
                ],
                [
                    'chatId' => '15555678@c.us',
                    'isAdmin' => false,
                    'isSuperAdmin' => false,
                    'name' => null,
                ],
            ],
        ];
    }

    public function testHappyPath(): void
    {
        $g = Group::fromArray(self::fixture());
        self::assertSame('120363021000000000@g.us', $g->id);
        self::assertSame('Team', $g->name);
        self::assertSame('Internal team chat', $g->description);
        self::assertSame('15551234@c.us', $g->owner);
        self::assertSame('2026-04-23T10:00:00Z', $g->createdAt);
        self::assertSame('2026-05-01T08:30:00Z', $g->lastMessageAt);
        self::assertSame(2, $g->participantCount);
        self::assertFalse($g->announce);
        self::assertTrue($g->restrict);
        self::assertIsArray($g->participants);
        self::assertCount(2, $g->participants);
        self::assertInstanceOf(GroupParticipant::class, $g->participants[0]);
        self::assertSame('Alice', $g->participants[0]->name);
    }

    public function testNullableParticipantsAccepted(): void
    {
        $raw = self::fixture();
        $raw['participants'] = null;
        $g = Group::fromArray($raw);
        self::assertNull($g->participants);
    }

    public function testMissingRequiredThrows(): void
    {
        $raw = self::fixture();
        unset($raw['id']);
        $this->expectException(ValidationError::class);
        Group::fromArray($raw);
    }

    public function testWrongTypeParticipantCountThrows(): void
    {
        $raw = self::fixture();
        $raw['participantCount'] = '2';
        $this->expectException(ValidationError::class);
        Group::fromArray($raw);
    }
}
