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
            'chatId' => '972544325389@c.us',
            'name' => 'Noam',
            'chatType' => 'contact',
            'pinned' => false,
            'archived' => false,
            'lastMessageAt' => '2026-04-29T12:34:56Z',
            'unreadCount' => 3,
            'markedUnread' => false,
        ];
    }

    public function testFromArrayHappyPath(): void
    {
        $c = Chat::fromArray(self::fixture());
        self::assertSame('972544325389@c.us', $c->chatId);
        self::assertSame('Noam', $c->name);
        self::assertSame('contact', $c->chatType);
        self::assertFalse($c->pinned);
        self::assertFalse($c->archived);
        self::assertSame('2026-04-29T12:34:56Z', $c->lastMessageAt);
        self::assertSame(3, $c->unreadCount);
        self::assertFalse($c->markedUnread);
    }

    public function testNullableFieldsAccepted(): void
    {
        $c = Chat::fromArray([
            'chatId' => '120363012345678901@g.us',
            'name' => null,
            'chatType' => 'group',
            'lastMessageAt' => null,
            'unreadCount' => null,
            'markedUnread' => true,
            'lastMessageText' => 'hi there',
            'lastMessageFromMe' => true,
        ]);
        self::assertNull($c->name);
        self::assertSame('group', $c->chatType);
        self::assertNull($c->pinned);
        self::assertNull($c->lastMessageAt);
        self::assertNull($c->unreadCount);
        self::assertTrue($c->markedUnread);
        self::assertSame('hi there', $c->lastMessageText);
        self::assertTrue($c->lastMessageFromMe);
    }

    public function testMissingRequiredChatIdThrows(): void
    {
        $this->expectException(ValidationError::class);
        $f = self::fixture();
        unset($f['chatId']);
        Chat::fromArray($f);
    }

    public function testInvalidChatTypeThrows(): void
    {
        $this->expectException(ValidationError::class);
        $f = self::fixture();
        $f['chatType'] = 'dm';
        Chat::fromArray($f);
    }

    public function testWrongTypeUnreadCountThrows(): void
    {
        $this->expectException(ValidationError::class);
        $f = self::fixture();
        $f['unreadCount'] = '3';
        Chat::fromArray($f);
    }
}
