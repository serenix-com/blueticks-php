<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\Group;
use PHPUnit\Framework\TestCase;

final class GroupTypeTest extends TestCase
{
    public function testHappyPathFull(): void
    {
        $g = Group::fromArray([
            'id' => '123@g.us',
            'name' => 'My Group',
            'participantCount' => 1,
            'announce' => false,
            'participants' => [
                ['chatId' => '1@c.us', 'isAdmin' => true, 'isSuperAdmin' => true],
            ],
        ]);
        self::assertSame('123@g.us', $g->id);
        self::assertCount(1, $g->participants ?? []);
    }

    public function testListItemSubsetHasNullParticipants(): void
    {
        $g = Group::fromArray(['id' => '123@g.us', 'name' => 'My Group']);
        self::assertNull($g->participants);
        self::assertNull($g->announce);
    }

    public function testMissingIdThrows(): void
    {
        $this->expectException(ValidationError::class);
        Group::fromArray(['name' => 'My Group']);
    }

    public function testWrongScalarThrows(): void
    {
        $this->expectException(ValidationError::class);
        Group::fromArray(['id' => 123]);
    }
}
