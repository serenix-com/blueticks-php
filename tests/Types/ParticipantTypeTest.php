<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\Participant;
use PHPUnit\Framework\TestCase;

final class ParticipantTypeTest extends TestCase
{
    public function testHappyPath(): void
    {
        $p = Participant::fromArray([
            'chatId' => '972544325389@c.us',
            'isAdmin' => true,
            'isSuperAdmin' => false,
        ]);
        self::assertSame('972544325389@c.us', $p->chatId);
        self::assertTrue($p->isAdmin);
        self::assertFalse($p->isSuperAdmin);
    }

    public function testIsSuperAdminOptional(): void
    {
        $p = Participant::fromArray([
            'chatId' => '972544325389@c.us',
            'isAdmin' => false,
        ]);
        self::assertNull($p->isSuperAdmin);
    }

    public function testMissingChatIdThrows(): void
    {
        $this->expectException(ValidationError::class);
        Participant::fromArray(['isAdmin' => true]);
    }

    public function testWrongTypeIsAdminThrows(): void
    {
        $this->expectException(ValidationError::class);
        Participant::fromArray(['chatId' => 'x@c.us', 'isAdmin' => 'yes']);
    }
}
