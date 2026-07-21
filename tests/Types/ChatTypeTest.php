<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\Chat;
use PHPUnit\Framework\TestCase;

final class ChatTypeTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function fixture(): array
    {
        return [
            'chatId' => '1@c.us',
            'name' => 'Alice',
            'chatType' => 'contact',
            'markedUnread' => false,
            'unreadCount' => 2,
        ];
    }

    public function testHappyPath(): void
    {
        $c = Chat::fromArray(self::fixture());
        self::assertSame('1@c.us', $c->chatId);
        self::assertSame('contact', $c->chatType);
        self::assertSame(2, $c->unreadCount);
    }

    public function testMissingRequiredThrows(): void
    {
        $f = self::fixture();
        unset($f['markedUnread']);
        $this->expectException(ValidationError::class);
        Chat::fromArray($f);
    }

    public function testInvalidChatTypeThrows(): void
    {
        $f = self::fixture();
        $f['chatType'] = 'banana';
        $this->expectException(ValidationError::class);
        Chat::fromArray($f);
    }

    public function testWrongScalarThrows(): void
    {
        $f = self::fixture();
        $f['chatId'] = 42;
        $this->expectException(ValidationError::class);
        Chat::fromArray($f);
    }
}
