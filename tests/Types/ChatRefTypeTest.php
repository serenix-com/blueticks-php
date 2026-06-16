<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\ChatRef;
use PHPUnit\Framework\TestCase;

final class ChatRefTypeTest extends TestCase
{
    public function testHappyPath(): void
    {
        $r = ChatRef::fromArray(['chatId' => '972544325389@c.us']);
        self::assertSame('972544325389@c.us', $r->chatId);
    }

    public function testMissingChatIdThrows(): void
    {
        $this->expectException(ValidationError::class);
        ChatRef::fromArray([]);
    }

    public function testWrongTypeThrows(): void
    {
        $this->expectException(ValidationError::class);
        ChatRef::fromArray(['chatId' => 12345]);
    }
}
