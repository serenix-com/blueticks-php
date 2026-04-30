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
            'is_group' => false,
            'last_message_at' => '2026-04-29T12:34:56Z',
            'unread_count' => 3,
        ];
    }

    public function testFromArrayHappyPath(): void
    {
        $c = Chat::fromArray(self::fixture());
        self::assertSame('972544325389@c.us', $c->id);
        self::assertSame('Noam', $c->name);
        self::assertFalse($c->is_group);
        self::assertSame('2026-04-29T12:34:56Z', $c->last_message_at);
        self::assertSame(3, $c->unread_count);
    }

    public function testNullableFieldsAccepted(): void
    {
        $c = Chat::fromArray([
            'id' => '120363012345678901@g.us',
            'name' => null,
            'is_group' => true,
            'last_message_at' => null,
            'unread_count' => null,
        ]);
        self::assertNull($c->name);
        self::assertTrue($c->is_group);
        self::assertNull($c->last_message_at);
        self::assertNull($c->unread_count);
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
        $f['is_group'] = 'no';
        Chat::fromArray($f);
    }

    public function testWrongTypeUnreadCountThrows(): void
    {
        $this->expectException(ValidationError::class);
        $f = self::fixture();
        $f['unread_count'] = '3';
        Chat::fromArray($f);
    }
}
