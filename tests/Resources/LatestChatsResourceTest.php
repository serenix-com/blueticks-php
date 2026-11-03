<?php

declare(strict_types=1);

namespace Blueticks\Tests\Resources;

use Blueticks\Blueticks;
use Blueticks\Errors\AuthenticationError;
use Blueticks\Tests\Helpers\MockTransport;
use Blueticks\Types\LatestChatsResponse;
use PHPUnit\Framework\TestCase;

final class LatestChatsResourceTest extends TestCase
{
    private function client(MockTransport $mock): Blueticks
    {
        return new Blueticks([
            'apiKey'         => 'bt_test_x',
            'baseUrl'        => 'https://api.blueticks.test',
            'httpClient'     => $mock->client(),
            'requestFactory' => $mock->factories(),
            'streamFactory'  => $mock->factories(),
            'retryBaseMs'    => 0,
            'retryCapMs'     => 0,
            'sleeper'        => function (int $_ms): void {
            },
        ]);
    }

    private function unauthorized(MockTransport $mock): void
    {
        $mock->enqueueJson(401, ['error' => [
            'code' => 'authentication_required',
            'message' => 'bad key',
            'request_id' => 'req_x',
        ]]);
    }

    private static function assertUnauthorized(AuthenticationError $e): void
    {
        self::assertSame(401, $e->statusCode);
        self::assertSame('authentication_required', $e->code);
        self::assertSame('req_x', $e->requestId);
    }

    public function testList(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => [
            'chats' => [[
                'chatId' => '15551234567@c.us',
                'name' => 'Alice',
                'chatType' => 'contact',
                'lastMessageAt' => '2026-11-02T10:00:00Z',
                'unreadCount' => 2,
                'markedUnread' => false,
                'messages' => [[
                    'waMessageKey' => ['fromMe' => false, '_serialized' => 'false_15551234567@c.us_ABC'],
                    'chatId' => '15551234567@c.us',
                    'from' => '15551234567@c.us',
                    'type' => 'chat',
                    'fromMe' => false,
                    'source' => 'vault',
                ]],
            ]],
            'totalChats' => 1,
            'numberOfChats' => 1,
            'numberOfMessages' => 1,
        ]]);

        $r = $this->client($mock)->latest_chats->list(['numberOfChats' => 5, 'numberOfMessages' => 1]);
        self::assertInstanceOf(LatestChatsResponse::class, $r);
        self::assertSame(1, $r->totalChats);
        self::assertSame('Alice', $r->chats[0]->name);
        self::assertSame('vault', $r->chats[0]->messages[0]->source);
        $uri = (string) $mock->requests()[0]->getUri();
        self::assertStringContainsString('/v1/latest-chats?', $uri);
        self::assertStringContainsString('numberOfChats=5', $uri);
    }

    public function testList401(): void
    {
        $mock = new MockTransport();
        $this->unauthorized($mock);

        try {
            $this->client($mock)->latest_chats->list();
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertUnauthorized($e);
        }
    }
}
