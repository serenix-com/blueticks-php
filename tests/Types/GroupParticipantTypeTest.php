<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\GroupParticipant;
use PHPUnit\Framework\TestCase;

final class GroupParticipantTypeTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function fixture(): array
    {
        return ['chatId' => '1@c.us', 'isAdmin' => true, 'isSuperAdmin' => false, 'name' => 'Alice'];
    }

    public function testHappyPath(): void
    {
        $p = GroupParticipant::fromArray(self::fixture());
        self::assertSame('1@c.us', $p->chatId);
        self::assertFalse($p->isSuperAdmin);
    }

    public function testMissingRequiredThrows(): void
    {
        $f = self::fixture();
        unset($f['isSuperAdmin']);
        $this->expectException(ValidationError::class);
        GroupParticipant::fromArray($f);
    }

    public function testWrongScalarThrows(): void
    {
        $f = self::fixture();
        $f['isAdmin'] = 1;
        $this->expectException(ValidationError::class);
        GroupParticipant::fromArray($f);
    }
}
