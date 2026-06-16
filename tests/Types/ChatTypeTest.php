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
            'id' => '972544325389@c.us',
            'name' => 'Noam',
            'isGroup' => false,
            'isNewsletter' => false,
            'lastMessageAt' => '2026-04-29T12:34:56Z',
            'unreadCount' => 3,
            'markedUnread' => false,
        ];
    }

    public function testFromArrayHappyPath(): void
    {
        $c = Chat::fromArray(self::fixture());
        self::assertSame('972544325389@c.us', $c->id);
        self::assertSame('Noam', $c->name);
        self::assertFalse($c->isGroup);
        self::assertFalse($c->isNewsletter);
        self::assertSame('2026-04-29T12:34:56Z', $c->lastMessageAt);
        self::assertSame(3, $c->unreadCount);
        self::assertFalse($c->markedUnread);
    }

    public function testNullableFieldsAccepted(): void
    {
        $c = Chat::fromArray([
            'id' => '120363012345678901@g.us',
            'name' => null,
            'isGroup' => true,
            'isNewsletter' => false,
            'lastMessageAt' => null,
            'unreadCount' => null,
            'markedUnread' => true,
        ]);
        self::assertNull($c->name);
        self::assertTrue($c->isGroup);
        self::assertNull($c->lastMessageAt);
        self::assertNull($c->unreadCount);
        self::assertTrue($c->markedUnread);
    }

    public function testMissingRequiredIdThrows(): void
    {
        $this->expectException(ValidationError::class);
        $f = self::fixture();
        unset($f['id']);
        Chat::fromArray($f);
    }

    public function testWrongTypeIsGroupThrows(): void
    {
        $this->expectException(ValidationError::class);
        $f = self::fixture();
        $f['isGroup'] = 'no';
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
