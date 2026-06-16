<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\GroupParticipant;
use PHPUnit\Framework\TestCase;

final class GroupParticipantTypeTest extends TestCase
{
    public function testHappyPath(): void
    {
        $p = GroupParticipant::fromArray([
            'chatId' => '972544325389@c.us',
            'isAdmin' => true,
            'isSuperAdmin' => false,
            'name' => 'Alice',
        ]);
        self::assertSame('972544325389@c.us', $p->chatId);
        self::assertTrue($p->isAdmin);
        self::assertFalse($p->isSuperAdmin);
        self::assertSame('Alice', $p->name);
    }

    public function testNullableNameAccepted(): void
    {
        $p = GroupParticipant::fromArray([
            'chatId' => '15551234@c.us',
            'isAdmin' => false,
            'isSuperAdmin' => false,
            'name' => null,
        ]);
        self::assertNull($p->name);
    }

    public function testMissingRequiredThrows(): void
    {
        $this->expectException(ValidationError::class);
        GroupParticipant::fromArray([
            'chatId' => 'x@c.us',
            'isAdmin' => true,
            'isSuperAdmin' => false,
            // 'name' missing
        ]);
    }

    public function testWrongTypeIsAdminThrows(): void
    {
        $this->expectException(ValidationError::class);
        GroupParticipant::fromArray([
            'chatId' => 'x@c.us',
            'isAdmin' => 'yes',
            'isSuperAdmin' => false,
            'name' => null,
        ]);
    }
}
