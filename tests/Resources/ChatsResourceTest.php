<?php

declare(strict_types=1);

namespace Blueticks\Tests\Resources;

use Blueticks\Blueticks;
use Blueticks\Errors\AuthenticationError;
use Blueticks\Tests\Helpers\MockTransport;
use Blueticks\Types\Chat;
use Blueticks\Types\ChatMedia;
use Blueticks\Types\ChatMessage;
use Blueticks\Types\Page;
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
        return [
            'id' => '1234@c.us',
            'name' => 'Alice',
            'is_group' => false,
            'last_message_at' => '2026-04-23T10:00:00Z',
            'unread_count' => 3,
        ];
    }

    /** @return array<string, mixed> */
    private static function participantFixture(): array
    {
        return [
            'chat_id' => '4321@c.us',
            'is_admin' => true,
            'is_super_admin' => false,
        ];
    }

    /** @return array<string, mixed> */
    private static function chatMessageFixture(): array
    {
        return [
            'key' => 'true_1234@c.us_ABCDEF',
            'chat_id' => '1234@c.us',
            'from' => '1234@c.us',
            'timestamp' => '2026-04-23T10:00:00Z',
            'text' => 'hello world',
            'type' => 'chat',
            'from_me' => false,
            'ack' => 3,
        ];
    }

    /** @return array<string, mixed> */
    private static function chatMediaFixture(): array
    {
        return [
            'url' => 'https://cdn.example.com/x.jpg',
            'mimetype' => 'image/jpeg',
            'filename' => 'x.jpg',
        ];
    }

    public function testListReturnsPageOfChats(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'data' => [self::chatFixture()],
            'has_more' => false,
            'next_cursor' => null,
        ]);

        $page = $this->client($mock)->chats->list(query: 'Alice', limit: 10);

        self::assertInstanceOf(Page::class, $page);
        self::assertCount(1, $page->data);
        self::assertInstanceOf(Chat::class, $page->data[0]);
        self::assertSame('Alice', $page->data[0]->name);
        self::assertFalse($page->has_more);

        $req = $mock->requests()[0];
        self::assertSame('GET', $req->getMethod());
        self::assertStringStartsWith(
            'https://api.blueticks.test/v1/chats?',
            (string) $req->getUri(),
        );
    }

    public function testGetReturnsChat(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, self::chatFixture());

        $chat = $this->client($mock)->chats->get('1234@c.us');

        self::assertInstanceOf(Chat::class, $chat);
        self::assertSame('Alice', $chat->name);
        self::assertFalse($chat->is_group);
        self::assertSame(3, $chat->unread_count);
    }

    public function testGet401MapsToAuthenticationError(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(401, [
            'error' => [
                'code' => 'authentication_required',
                'message' => 'bad key',
                'request_id' => 'req_x',
            ],
        ]);

        try {
            $this->client($mock)->chats->get('1234@c.us');
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertSame(401, $e->statusCode);
            self::assertSame('authentication_required', $e->code);
            self::assertSame('req_x', $e->requestId);
        }
    }

    public function testListParticipantsReturnsPage(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'data' => [self::participantFixture()],
            'has_more' => false,
            'next_cursor' => null,
        ]);

        $page = $this->client($mock)->chats->listParticipants('1234@g.us');

        self::assertInstanceOf(Page::class, $page);
        self::assertCount(1, $page->data);
        self::assertInstanceOf(Participant::class, $page->data[0]);
        self::assertTrue($page->data[0]->is_admin);
    }

    public function testListMessagesReturnsPageOfChatMessages(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'data' => [self::chatMessageFixture()],
            'has_more' => true,
            'next_cursor' => 'cur_123',
        ]);

        $page = $this->client($mock)->chats->listMessages('1234@c.us', [
            'mode' => 'history',
            'message_types' => ['document'],
        ]);

        self::assertInstanceOf(Page::class, $page);
        self::assertCount(1, $page->data);
        self::assertInstanceOf(ChatMessage::class, $page->data[0]);
        self::assertSame('hello world', $page->data[0]->text);
        self::assertTrue($page->has_more);
        self::assertSame('cur_123', $page->next_cursor);
    }

    public function testGetMessageReturnsChatMessage(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, self::chatMessageFixture());

        $msg = $this->client($mock)->chats->getMessage('1234@c.us', 'true_1234@c.us_ABCDEF');

        self::assertInstanceOf(ChatMessage::class, $msg);
        self::assertSame('hello world', $msg->text);
        self::assertSame('chat', $msg->type);
    }

    public function testGetMediaReturnsChatMedia(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, self::chatMediaFixture());

        $media = $this->client($mock)->chats->getMedia('1234@c.us', 'true_1234@c.us_ABCDEF');

        self::assertInstanceOf(ChatMedia::class, $media);
        self::assertSame('image/jpeg', $media->mimetype);
        self::assertSame('x.jpg', $media->filename);
    }
}
