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
            'chat_id' => '972544325389@c.us',
            'is_admin' => true,
            'is_super_admin' => false,
            'name' => 'Alice',
        ]);
        self::assertSame('972544325389@c.us', $p->chat_id);
        self::assertTrue($p->is_admin);
        self::assertFalse($p->is_super_admin);
        self::assertSame('Alice', $p->name);
    }

    public function testNullableNameAccepted(): void
    {
        $p = GroupParticipant::fromArray([
            'chat_id' => '15551234@c.us',
            'is_admin' => false,
            'is_super_admin' => false,
            'name' => null,
        ]);
        self::assertNull($p->name);
    }

    public function testMissingRequiredThrows(): void
    {
        $this->expectException(ValidationError::class);
        GroupParticipant::fromArray([
            'chat_id' => 'x@c.us',
            'is_admin' => true,
            'is_super_admin' => false,
            // 'name' missing
        ]);
    }

    public function testWrongTypeIsAdminThrows(): void
    {
        $this->expectException(ValidationError::class);
        GroupParticipant::fromArray([
            'chat_id' => 'x@c.us',
            'is_admin' => 'yes',
            'is_super_admin' => false,
            'name' => null,
        ]);
    }
}
