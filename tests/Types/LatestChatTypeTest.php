<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\LatestChat;
use PHPUnit\Framework\TestCase;

final class LatestChatTypeTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function fixture(): array
    {
        return [
            'chatId' => 'chatId_value',
            'name' => 'name_value',
            'chatType' => 'contact',
            'pinned' => true,
            'archived' => true,
            'lastMessageAt' => '2026-11-01T10:00:00Z',
            'unreadCount' => 3,
            'markedUnread' => true,
            'lastMessageText' => 'lastMessageText_value',
            'lastMessageFromMe' => true,
            'messages' => [
                [
                    'waMessageKey' => [
                        'fromMe' => true,
                        'id' => 'ABC',
                    ],
                    'chatId' => '1@c.us',
                    'from' => '1@c.us',
                    'type' => 'chat',
                    'fromMe' => true,
                ],
            ],
        ];
    }


    public function testHappyPath(): void
    {
        $x = LatestChat::fromArray(self::fixture());
        self::assertSame(self::fixture()['chatId'], $x->chatId);
        self::assertSame(self::fixture()['name'], $x->name);
        self::assertSame(self::fixture()['chatType'], $x->chatType);
        self::assertSame(self::fixture()['pinned'], $x->pinned);
    }

    public function testMissingRequiredThrows(): void
    {
        $data = self::fixture();
        unset($data['chatId']);
        $this->expectException(ValidationError::class);
        LatestChat::fromArray($data);
    }

    public function testWrongScalarThrows(): void
    {
        $data = self::fixture();
        $data['chatId'] = 12345;
        $this->expectException(ValidationError::class);
        LatestChat::fromArray($data);
    }
}
