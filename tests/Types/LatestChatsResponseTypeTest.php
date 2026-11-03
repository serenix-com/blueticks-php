<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\LatestChatsResponse;
use PHPUnit\Framework\TestCase;

final class LatestChatsResponseTypeTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function fixture(): array
    {
        return [
            'chats' => [
                [
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
                ],
            ],
            'totalChats' => 3,
            'numberOfChats' => 3,
            'numberOfMessages' => 3,
        ];
    }


    public function testHappyPath(): void
    {
        $x = LatestChatsResponse::fromArray(self::fixture());
        self::assertSame(self::fixture()['totalChats'], $x->totalChats);
        self::assertSame(self::fixture()['numberOfChats'], $x->numberOfChats);
        self::assertSame(self::fixture()['numberOfMessages'], $x->numberOfMessages);
    }

    public function testMissingRequiredThrows(): void
    {
        $data = self::fixture();
        unset($data['totalChats']);
        $this->expectException(ValidationError::class);
        LatestChatsResponse::fromArray($data);
    }

    public function testWrongScalarThrows(): void
    {
        $data = self::fixture();
        $data['totalChats'] = 'nope';
        $this->expectException(ValidationError::class);
        LatestChatsResponse::fromArray($data);
    }
}
