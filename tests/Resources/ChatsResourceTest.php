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
use Blueticks\Types\Message;
use Blueticks\Types\MessageAck;
use Blueticks\Types\OkResponse;
use Blueticks\Types\Page;
use Blueticks\Types\Participant;
use Blueticks\Types\SendInChatRequest;
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
            'isGroup' => false,
            'isNewsletter' => false,
            'lastMessageAt' => '2026-04-23T10:00:00Z',
            'unreadCount' => 3,
            'markedUnread' => false,
        ];
    }

    /** @return array<string, mixed> */
    private static function participantFixture(): array
    {
        return [
            'chatId' => '4321@c.us',
            'isAdmin' => true,
            'isSuperAdmin' => false,
        ];
    }

    /** @return array<string, mixed> */
    private static function chatMessageFixture(): array
    {
        return [
            'key' => 'true_1234@c.us_ABCDEF',
            'chatId' => '1234@c.us',
            'from' => '1234@c.us',
            'timestamp' => '2026-04-23T10:00:00Z',
            'text' => 'hello world',
            'type' => 'chat',
            'fromMe' => false,
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
            'hasMore' => false,
            'nextCursor' => null,
        ]);

        $page = $this->client($mock)->chats->list(query: 'Alice', limit: 10);

        self::assertInstanceOf(Page::class, $page);
        self::assertCount(1, $page->data);
        self::assertInstanceOf(Chat::class, $page->data[0]);
        self::assertSame('Alice', $page->data[0]->name);
        self::assertFalse($page->hasMore);

        $req = $mock->requests()[0];
        self::assertSame('GET', $req->getMethod());
        self::assertStringStartsWith(
            'https://api.blueticks.test/v1/chats?',
            (string) $req->getUri(),
        );
    }

    public function testRetrieveReturnsChat(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, self::chatFixture());

        $chat = $this->client($mock)->chats->retrieve('1234@c.us');

        self::assertInstanceOf(Chat::class, $chat);
        self::assertSame('Alice', $chat->name);
        self::assertFalse($chat->isGroup);
        self::assertSame(3, $chat->unreadCount);
    }

    public function testRetrieve401MapsToAuthenticationError(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(401, [
            'error' => [
                'code'       => 'authentication_required',
                'message'    => 'bad key',
                'requestId' => 'req_x',
            ],
        ]);

        try {
            $this->client($mock)->chats->retrieve('1234@c.us');
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
            'hasMore' => false,
            'nextCursor' => null,
        ]);

        $page = $this->client($mock)->chats->listParticipants('1234@g.us');

        self::assertInstanceOf(Page::class, $page);
        self::assertCount(1, $page->data);
        self::assertInstanceOf(Participant::class, $page->data[0]);
        self::assertTrue($page->data[0]->isAdmin);
    }

    public function testListMessagesReturnsPageOfChatMessages(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'data' => [self::chatMessageFixture()],
            'hasMore' => true,
            'nextCursor' => 'cur_123',
        ]);

        $page = $this->client($mock)->chats->listMessages('1234@c.us', [
            'order' => 'asc',
            'messageTypes' => ['document'],
        ]);

        self::assertInstanceOf(Page::class, $page);
        self::assertCount(1, $page->data);
        self::assertInstanceOf(ChatMessage::class, $page->data[0]);
        self::assertSame('hello world', $page->data[0]->text);
        self::assertTrue($page->hasMore);
        self::assertSame('cur_123', $page->nextCursor);

        $req = $mock->requests()[0];
        self::assertStringStartsWith(
            'https://api.blueticks.test/v1/messages?',
            (string) $req->getUri(),
        );
        self::assertStringContainsString('chatId=1234%40c.us', (string) $req->getUri());
    }

    public function testGetMessageReturnsChatMessage(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, self::chatMessageFixture());

        $msg = $this->client($mock)->chats->getMessage('true_1234@c.us_ABCDEF', '1234@c.us');

        self::assertInstanceOf(ChatMessage::class, $msg);
        self::assertSame('hello world', $msg->text);
        self::assertSame('chat', $msg->type);

        $req = $mock->requests()[0];
        self::assertSame('GET', $req->getMethod());
        self::assertStringStartsWith(
            'https://api.blueticks.test/v1/messages/true_1234%40c.us_ABCDEF',
            (string) $req->getUri(),
        );
        self::assertStringContainsString('chatId=1234%40c.us', (string) $req->getUri());
    }

    public function testGetMediaReturnsChatMedia(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, self::chatMediaFixture());

        $media = $this->client($mock)->chats->getMedia('true_1234@c.us_ABCDEF');

        self::assertInstanceOf(ChatMedia::class, $media);
        self::assertSame('image/jpeg', $media->mimetype);
        self::assertSame('x.jpg', $media->filename);

        $req = $mock->requests()[0];
        self::assertSame(
            'https://api.blueticks.test/v1/messages/media/true_1234%40c.us_ABCDEF',
            (string) $req->getUri(),
        );
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
        $mock->enqueueJson(200, ['chatId' => '1234@c.us']);

        $ref = $this->client($mock)->chats->open('1234@c.us');

        self::assertInstanceOf(ChatRef::class, $ref);
        self::assertSame('1234@c.us', $ref->chatId);

        $req = $mock->requests()[0];
        self::assertSame('POST', $req->getMethod());
    }

    public function testGetMessageAckReturnsMessageAck(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['ack' => 3]);

        $a = $this->client($mock)->chats->getMessageAck('true_1234@c.us_ABCDEF');

        self::assertInstanceOf(MessageAck::class, $a);
        self::assertSame(3, $a->ack);

        $req = $mock->requests()[0];
        self::assertSame('GET', $req->getMethod());
        self::assertSame(
            'https://api.blueticks.test/v1/messages/ack/true_1234%40c.us_ABCDEF',
            (string) $req->getUri(),
        );
    }

    public function testReactReturnsOkResponseAndPostsEmoji(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['ok' => true]);

        $r = $this->client($mock)->chats->react('true_1234@c.us_ABCDEF', "\u{1F44D}", '1234@c.us');

        self::assertInstanceOf(OkResponse::class, $r);

        $req = $mock->requests()[0];
        self::assertSame('POST', $req->getMethod());
        self::assertStringStartsWith(
            'https://api.blueticks.test/v1/messages/reactions/true_1234%40c.us_ABCDEF',
            (string) $req->getUri(),
        );
        self::assertStringContainsString('chatId=1234%40c.us', (string) $req->getUri());
        /** @var array<string, mixed> $body */
        $body = json_decode((string) $req->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(["\u{1F44D}"], [$body['emoji']]);
    }

    public function testLoadOlderMessagesReturnsResponse(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'totalMessages' => 1200,
            'added' => 50,
            'canLoadMore' => true,
        ]);

        $r = $this->client($mock)->chats->loadOlderMessages('1234@c.us');

        self::assertInstanceOf(LoadOlderMessagesResponse::class, $r);
        self::assertSame(50, $r->added);
        self::assertTrue($r->canLoadMore);

        $req = $mock->requests()[0];
        self::assertSame('POST', $req->getMethod());
        self::assertSame(
            'https://api.blueticks.test/v1/messages/load_older/1234%40c.us',
            (string) $req->getUri(),
        );
    }

    /** @return array<string, mixed> */
    private static function sentMessageFixture(): array
    {
        return [
            'id'             => 'snt_1',
            'key'            => 'true_1234@c.us_ABCDEF',
            'to'             => '1234@c.us',
            'from'           => null,
            'type'           => 'text',
            'text'           => 'hello chat',
            'mediaUrl'      => null,
            'mediaKind'     => null,
            'pollQuestion'  => null,
            'status'         => 'confirmed',
            'sendAt'        => null,
            'createdAt'     => '2026-04-23T10:00:00Z',
            'confirmedAt'   => '2026-04-23T10:00:00Z',
            'receivedAt'    => null,
            'readAt'        => null,
            'playedAt'      => null,
            'failedAt'      => null,
            'failureReason' => null,
        ];
    }

    public function testSendMessageText(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(201, self::sentMessageFixture());

        $msg = $this->client($mock)->chats->sendMessage('1234@c.us', [
            'type' => SendInChatRequest::TYPE_TEXT,
            'text' => 'hello chat',
        ]);

        self::assertInstanceOf(Message::class, $msg);
        self::assertSame('1234@c.us', $msg->to);
        self::assertSame('text', $msg->type);
        self::assertSame('hello chat', $msg->text);

        $req = $mock->requests()[0];
        self::assertSame('POST', $req->getMethod());
        self::assertSame(
            'https://api.blueticks.test/v1/messages/1234%40c.us',
            (string) $req->getUri(),
        );
        /** @var array<string, mixed> $body */
        $body = json_decode((string) $req->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('text', $body['type']);
        self::assertSame('hello chat', $body['text']);
        self::assertArrayNotHasKey('to', $body);
        self::assertArrayNotHasKey('sendAt', $body);
    }

    public function testSendMessageMedia(): void
    {
        $fixture = self::sentMessageFixture();
        $fixture['type'] = 'media';
        $fixture['text'] = null;
        $fixture['mediaUrl'] = 'https://cdn.example.com/receipt.pdf';
        $fixture['mediaKind'] = 'document';
        $mock = new MockTransport();
        $mock->enqueueJson(201, $fixture);

        $msg = $this->client($mock)->chats->sendMessage('1234@c.us', [
            'type'          => SendInChatRequest::TYPE_MEDIA,
            'mediaUrl'      => 'https://cdn.example.com/receipt.pdf',
            'mediaKind'     => 'document',
            'mediaFilename' => 'receipt.pdf',
        ]);

        self::assertInstanceOf(Message::class, $msg);
        self::assertSame('media', $msg->type);
        self::assertSame('https://cdn.example.com/receipt.pdf', $msg->mediaUrl);

        /** @var array<string, mixed> $body */
        $body = json_decode((string) $mock->requests()[0]->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('media', $body['type']);
        self::assertSame('https://cdn.example.com/receipt.pdf', $body['mediaUrl']);
        self::assertSame('document', $body['mediaKind']);
    }

    public function testSendMessagePoll(): void
    {
        $fixture = self::sentMessageFixture();
        $fixture['type'] = 'poll';
        $fixture['text'] = null;
        $fixture['pollQuestion'] = 'Pizza?';
        $mock = new MockTransport();
        $mock->enqueueJson(201, $fixture);

        $msg = $this->client($mock)->chats->sendMessage('1234@c.us', [
            'type'         => SendInChatRequest::TYPE_POLL,
            'pollQuestion' => 'Pizza?',
            'pollOptions'  => ['Yes', 'No'],
        ]);

        self::assertInstanceOf(Message::class, $msg);
        self::assertSame('poll', $msg->type);
        self::assertSame('Pizza?', $msg->pollQuestion);

        /** @var array<string, mixed> $body */
        $body = json_decode((string) $mock->requests()[0]->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('poll', $body['type']);
        self::assertSame('Pizza?', $body['pollQuestion']);
        self::assertSame(['Yes', 'No'], $body['pollOptions']);
    }

    public function testSendMessagePropagatesIdempotencyKey(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(201, self::sentMessageFixture());

        $this->client($mock)->chats->sendMessage('1234@c.us', [
            'type'            => 'text',
            'text'            => 'hi',
            'idempotencyKey' => 'key_chat_abc',
        ]);

        $req = $mock->requests()[0];
        self::assertSame('key_chat_abc', $req->getHeaderLine('Idempotency-Key'));
        /** @var array<string, mixed> $body */
        $body = json_decode((string) $req->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayNotHasKey('idempotencyKey', $body);
    }

    public function testSendMessage401MapsToAuthenticationError(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(401, [
            'error' => [
                'code'       => 'authentication_required',
                'message'    => 'bad key',
                'requestId' => 'req_send_chat',
            ],
        ]);

        try {
            $this->client($mock)->chats->sendMessage('1234@c.us', [
                'type' => 'text',
                'text' => 'hi',
            ]);
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertSame(401, $e->statusCode);
            self::assertSame('authentication_required', $e->code);
            self::assertSame('req_send_chat', $e->requestId);
        }
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
            'https://api.blueticks.test/v1/messages/acks',
            (string) $req->getUri(),
        );
        /** @var array<string, mixed> $body */
        $body = json_decode((string) $req->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(
            ['true_1234@c.us_ABC', 'true_1234@c.us_DEF'],
            $body['messageKeys'],
        );
    }
}
