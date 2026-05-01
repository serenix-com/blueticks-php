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
            'created_at' => '2026-04-23T10:00:00Z',
            'last_message_at' => '2026-05-01T08:30:00Z',
            'participant_count' => 2,
            'announce' => false,
            'restrict' => true,
            'participants' => [
                [
                    'chat_id' => '15551234@c.us',
                    'is_admin' => true,
                    'is_super_admin' => true,
                    'name' => 'Alice',
                ],
                [
                    'chat_id' => '15555678@c.us',
                    'is_admin' => false,
                    'is_super_admin' => false,
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
        self::assertSame('2026-04-23T10:00:00Z', $g->created_at);
        self::assertSame('2026-05-01T08:30:00Z', $g->last_message_at);
        self::assertSame(2, $g->participant_count);
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
        $raw['participant_count'] = '2';
        $this->expectException(ValidationError::class);
        Group::fromArray($raw);
    }
}
