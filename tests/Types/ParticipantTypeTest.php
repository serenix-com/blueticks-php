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
            'chatId' => '1@c.us',
            'name' => 'Alice',
            'isAdmin' => true,
            'isSuperAdmin' => false,
        ]);
        self::assertSame('1@c.us', $p->chatId);
        self::assertTrue($p->isAdmin);
        self::assertFalse($p->isSuperAdmin);
    }

    public function testMissingRequiredThrows(): void
    {
        $this->expectException(ValidationError::class);
        Participant::fromArray(['chatId' => '1@c.us']);
    }

    public function testWrongScalarThrows(): void
    {
        $this->expectException(ValidationError::class);
        Participant::fromArray(['chatId' => '1@c.us', 'isAdmin' => 'yes']);
    }
}
