<?php

declare(strict_types=1);

namespace Blueticks\Tests\Resources;

use Blueticks\Blueticks;
use Blueticks\Errors\AuthenticationError;
use Blueticks\Tests\Helpers\MockTransport;
use Blueticks\Types\Chat;
use Blueticks\Types\OkResponse;
use Blueticks\Types\Participant;
use PHPUnit\Framework\TestCase;

final class ChatsResourceTest extends TestCase
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

    /** @return array<string, mixed> */
    private static function chatFixture(): array
    {
        return ['chatId' => '15551234567@c.us', 'name' => 'Alice', 'chatType' => 'contact', 'markedUnread' => false];
    }

    public function testList(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'success' => true,
            'data' => [self::chatFixture()],
            'limit' => 25,
            'skip' => 0,
            'total' => 1,
        ]);

        $page = $this->client($mock)->chats->list(['filter' => 'contacts', 'includeArchive' => true]);
        self::assertInstanceOf(Chat::class, $page->data[0]);
        self::assertSame('contact', $page->data[0]->chatType);

        $uri = (string) $mock->requests()[0]->getUri();
        self::assertStringContainsString('filter=contacts', $uri);
        self::assertStringContainsString('includeArchive=true', $uri);
    }

    public function testRetrieve(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => self::chatFixture()]);

        $c = $this->client($mock)->chats->retrieve('15551234567@c.us');
        self::assertSame('15551234567@c.us', $c->chatId);
    }

    public function testListParticipants(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'success' => true,
            'data' => [['chatId' => '1@c.us', 'name' => 'A', 'isAdmin' => true, 'isSuperAdmin' => false]],
            'limit' => 25,
            'skip' => 0,
            'total' => 1,
        ]);

        $page = $this->client($mock)->chats->listParticipants('123@g.us');
        self::assertInstanceOf(Participant::class, $page->data[0]);
        self::assertTrue($page->data[0]->isAdmin);
    }

    public function testArchiveUnarchiveMarkRead(): void
    {
        foreach (['archive', 'unarchive', 'markRead'] as $method) {
            $mock = new MockTransport();
            $mock->enqueueJson(200, ['success' => true, 'data' => ['ok' => true]]);
            $r = $this->client($mock)->chats->{$method}('15551234567@c.us');
            self::assertInstanceOf(OkResponse::class, $r);
            self::assertSame('POST', $mock->requests()[0]->getMethod());
        }
    }

    public function testRetrieve401(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(401, ['error' => [
            'code' => 'authentication_required',
            'message' => 'bad key',
            'request_id' => 'req_x',
        ]]);

        try {
            $this->client($mock)->chats->retrieve('15551234567@c.us');
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertSame('req_x', $e->requestId);
        }
    }
}
