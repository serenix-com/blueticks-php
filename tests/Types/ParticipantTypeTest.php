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
            'chat_id' => '972544325389@c.us',
            'is_admin' => true,
            'is_super_admin' => false,
        ]);
        self::assertSame('972544325389@c.us', $p->chat_id);
        self::assertTrue($p->is_admin);
        self::assertFalse($p->is_super_admin);
    }

    public function testIsSuperAdminOptional(): void
    {
        $p = Participant::fromArray([
            'chat_id' => '972544325389@c.us',
            'is_admin' => false,
        ]);
        self::assertNull($p->is_super_admin);
    }

    public function testMissingChatIdThrows(): void
    {
        $this->expectException(ValidationError::class);
        Participant::fromArray(['is_admin' => true]);
    }

    public function testWrongTypeIsAdminThrows(): void
    {
        $this->expectException(ValidationError::class);
        Participant::fromArray(['chat_id' => 'x@c.us', 'is_admin' => 'yes']);
    }
}
