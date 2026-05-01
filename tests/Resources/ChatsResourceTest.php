<?php

declare(strict_types=1);

namespace Blueticks\Tests\Resources;

use Blueticks\Blueticks;
use Blueticks\Errors\AuthenticationError;
use Blueticks\Tests\Helpers\MockTransport;
use Blueticks\Types\BatchMessageAcksResponse;
use Blueticks\Types\Chat;
use Blueticks\Types\ChatMedia;
use Blueticks\Types\ChatMessage;
use Blueticks\Types\ChatRef;
use Blueticks\Types\LoadOlderMessagesResponse;
use Blueticks\Types\MediaUrlResponse;
use Blueticks\Types\MessageAck;
use Blueticks\Types\OkResponse;
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

    public function testMarkReadReturnsOkResponse(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['ok' => true]);

        $r = $this->client($mock)->chats->markRead('1234@c.us');

        self::assertInstanceOf(OkResponse::class, $r);
        self::assertTrue($r->ok);

        $req = $mock->requests()[0];
        self::assertSame('POST', $req->getMethod());
        self::assertSame(
            'https://api.blueticks.test/v1/chats/1234%40c.us/mark_read',
            (string) $req->getUri(),
        );
    }

    public function testOpenReturnsChatRef(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['chat_id' => '1234@c.us']);

        $ref = $this->client($mock)->chats->open('1234@c.us');

        self::assertInstanceOf(ChatRef::class, $ref);
        self::assertSame('1234@c.us', $ref->chat_id);

        $req = $mock->requests()[0];
        self::assertSame('POST', $req->getMethod());
    }

    public function testGetMessageAckReturnsMessageAck(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['ack' => 3]);

        $a = $this->client($mock)->chats->getMessageAck('1234@c.us', 'true_1234@c.us_ABCDEF');

        self::assertInstanceOf(MessageAck::class, $a);
        self::assertSame(3, $a->ack);
    }

    public function testReactReturnsOkResponseAndPostsEmoji(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['ok' => true]);

        $r = $this->client($mock)->chats->react('1234@c.us', 'true_1234@c.us_ABCDEF', "\u{1F44D}");

        self::assertInstanceOf(OkResponse::class, $r);

        $req = $mock->requests()[0];
        self::assertSame('POST', $req->getMethod());
        /** @var array<string, mixed> $body */
        $body = json_decode((string) $req->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(["\u{1F44D}"], [$body['emoji']]);
    }

    public function testLoadOlderMessagesReturnsResponse(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'total_messages' => 1200,
            'added' => 50,
            'can_load_more' => true,
        ]);

        $r = $this->client($mock)->chats->loadOlderMessages('1234@c.us');

        self::assertInstanceOf(LoadOlderMessagesResponse::class, $r);
        self::assertSame(50, $r->added);
        self::assertTrue($r->can_load_more);
    }

    public function testGetMediaUrlReturnsMediaUrlResponse(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['url' => 'https://cdn.example.com/x.jpg']);

        $r = $this->client($mock)->chats->getMediaUrl('1234@c.us', 'true_1234@c.us_ABCDEF');

        self::assertInstanceOf(MediaUrlResponse::class, $r);
        self::assertSame('https://cdn.example.com/x.jpg', $r->url);
    }

    public function testBatchMessageAcksPostsKeysAndReturnsRows(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'data' => [
                ['key' => 'true_1234@c.us_ABC', 'ack' => 3],
                ['key' => 'true_1234@c.us_DEF', 'ack' => 1],
            ],
        ]);

        $r = $this->client($mock)->chats->batchMessageAcks([
            'true_1234@c.us_ABC',
            'true_1234@c.us_DEF',
        ]);

        self::assertInstanceOf(BatchMessageAcksResponse::class, $r);
        self::assertCount(2, $r->data);
        self::assertSame('true_1234@c.us_ABC', $r->data[0]->key);
        self::assertSame(3, $r->data[0]->ack);
        self::assertSame('true_1234@c.us_DEF', $r->data[1]->key);
        self::assertSame(1, $r->data[1]->ack);

        $req = $mock->requests()[0];
        self::assertSame('POST', $req->getMethod());
        self::assertSame(
            'https://api.blueticks.test/v1/chats/message_acks',
            (string) $req->getUri(),
        );
        /** @var array<string, mixed> $body */
        $body = json_decode((string) $req->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(
            ['true_1234@c.us_ABC', 'true_1234@c.us_DEF'],
            $body['message_keys'],
        );
    }
}
