<?php

declare(strict_types=1);

namespace Blueticks\Tests\Resources;

use Blueticks\Blueticks;
use Blueticks\Errors\AuthenticationError;
use Blueticks\Tests\Helpers\MockTransport;
use Blueticks\Types\Message;
use Blueticks\Types\Page;
use PHPUnit\Framework\TestCase;

final class ScheduledMessagesResourceTest extends TestCase
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
    private static function messageFixture(): array
    {
        return [
            'id'             => 'msg_1',
            'key'            => null,
            'to'             => '+15551234567',
            'from'           => null,
            'type'           => 'text',
            'text'           => 'hello',
            'media_url'      => null,
            'media_kind'     => null,
            'poll_question'  => null,
            'status'         => 'pending',
            'send_at'        => null,
            'created_at'     => '2026-04-23T10:00:00Z',
            'confirmed_at'   => null,
            'received_at'    => null,
            'read_at'        => null,
            'played_at'      => null,
            'failed_at'      => null,
            'failure_reason' => null,
        ];
    }

    public function testCreateText(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(201, self::messageFixture());

        $msg = $this->client($mock)->scheduled_messages->create([
            'type' => 'text',
            'to'   => '+15551234567',
            'text' => 'hello',
        ]);

        self::assertInstanceOf(Message::class, $msg);
        self::assertSame('msg_1', $msg->id);
        self::assertSame('text', $msg->type);

        $req = $mock->requests()[0];
        self::assertSame('POST', $req->getMethod());
        self::assertSame(
            'https://api.blueticks.test/v1/scheduled-messages',
            (string) $req->getUri(),
        );
        /** @var array<string, mixed> $decoded */
        $decoded = json_decode((string) $req->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('text', $decoded['type']);
        self::assertSame('+15551234567', $decoded['to']);
        self::assertSame('hello', $decoded['text']);
    }

    public function testCreate401MapsToAuthenticationError(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(401, [
            'error' => [
                'code'       => 'authentication_required',
                'message'    => 'bad key',
                'request_id' => 'req_x',
            ],
        ]);

        try {
            $this->client($mock)->scheduled_messages->create([
                'type' => 'text',
                'to'   => '+15551234567',
                'text' => 'hello',
            ]);
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertSame(401, $e->statusCode);
            self::assertSame('authentication_required', $e->code);
            self::assertSame('req_x', $e->requestId);
        }
    }

    public function testCreateMedia(): void
    {
        $fixture = self::messageFixture();
        $fixture['type'] = 'media';
        $fixture['media_url'] = 'https://cdn.example.com/receipt.pdf';
        $fixture['media_kind'] = 'document';
        $mock = new MockTransport();
        $mock->enqueueJson(201, $fixture);

        $msg = $this->client($mock)->scheduled_messages->create([
            'type'  => 'media',
            'to'    => '+15551234567',
            'media' => [
                'url'      => 'https://cdn.example.com/receipt.pdf',
                'kind'     => 'document',
                'filename' => 'receipt.pdf',
            ],
        ]);

        self::assertInstanceOf(Message::class, $msg);
        self::assertSame('media', $msg->type);
        self::assertSame('https://cdn.example.com/receipt.pdf', $msg->media_url);

        /** @var array<string, mixed> $body */
        $body = json_decode((string) $mock->requests()[0]->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('media', $body['type']);
        self::assertSame('https://cdn.example.com/receipt.pdf', $body['media']['url']);
    }

    public function testCreatePoll(): void
    {
        $fixture = self::messageFixture();
        $fixture['type'] = 'poll';
        $fixture['poll_question'] = 'Pizza?';
        $mock = new MockTransport();
        $mock->enqueueJson(201, $fixture);

        $msg = $this->client($mock)->scheduled_messages->create([
            'type' => 'poll',
            'to'   => '+15551234567',
            'poll' => [
                'question' => 'Pizza?',
                'options'  => ['Yes', 'No'],
            ],
        ]);

        self::assertInstanceOf(Message::class, $msg);
        self::assertSame('poll', $msg->type);
        self::assertSame('Pizza?', $msg->poll_question);
    }

    public function testCreatePropagatesIdempotencyKey(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, self::messageFixture());

        $this->client($mock)->scheduled_messages->create([
            'type'             => 'text',
            'to'               => '+15551234567',
            'text'             => 'hi',
            'idempotency_key'  => 'key_abc',
        ]);

        $req = $mock->requests()[0];
        self::assertSame('key_abc', $req->getHeaderLine('Idempotency-Key'));
        /** @var array<string, mixed> $decoded */
        $decoded = json_decode((string) $req->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayNotHasKey('idempotency_key', $decoded);
    }

    public function testList(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'data' => [self::messageFixture()],
            'has_more' => false,
            'next_cursor' => null,
        ]);

        $page = $this->client($mock)->scheduled_messages->list();
        self::assertInstanceOf(Page::class, $page);
        self::assertCount(1, $page->data);
        self::assertInstanceOf(Message::class, $page->data[0]);
        self::assertSame('msg_1', $page->data[0]->id);

        $req = $mock->requests()[0];
        self::assertSame('GET', $req->getMethod());
        self::assertSame(
            'https://api.blueticks.test/v1/scheduled-messages',
            (string) $req->getUri(),
        );
    }

    public function testListWithQuery(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'data' => [],
            'has_more' => false,
            'next_cursor' => null,
        ]);

        $this->client($mock)->scheduled_messages->list(limit: 25, cursor: 'cur_x');
        $req = $mock->requests()[0];
        self::assertSame('GET', $req->getMethod());
        $uri = (string) $req->getUri();
        self::assertStringStartsWith(
            'https://api.blueticks.test/v1/scheduled-messages?',
            $uri,
        );
        self::assertStringContainsString('limit=25', $uri);
        self::assertStringContainsString('cursor=cur_x', $uri);
    }

    public function testList401MapsToAuthenticationError(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(401, [
            'error' => [
                'code'       => 'authentication_required',
                'message'    => 'bad key',
                'request_id' => 'req_x',
            ],
        ]);

        try {
            $this->client($mock)->scheduled_messages->list();
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertSame(401, $e->statusCode);
            self::assertSame('authentication_required', $e->code);
            self::assertSame('req_x', $e->requestId);
        }
    }

    public function testRetrieve(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, self::messageFixture());

        $msg = $this->client($mock)->scheduled_messages->retrieve('msg_1');

        self::assertInstanceOf(Message::class, $msg);
        self::assertSame('msg_1', $msg->id);
        $req = $mock->requests()[0];
        self::assertSame('GET', $req->getMethod());
        self::assertSame(
            'https://api.blueticks.test/v1/scheduled-messages/msg_1',
            (string) $req->getUri(),
        );
    }

    public function testRetrieve401MapsToAuthenticationError(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(401, [
            'error' => [
                'code'       => 'authentication_required',
                'message'    => 'bad key',
                'request_id' => 'req_x',
            ],
        ]);

        try {
            $this->client($mock)->scheduled_messages->retrieve('msg_1');
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertSame(401, $e->statusCode);
            self::assertSame('authentication_required', $e->code);
            self::assertSame('req_x', $e->requestId);
        }
    }

    public function testUpdatePatchesMessage(): void
    {
        $mock = new MockTransport();
        $fixture = self::messageFixture();
        $fixture['text'] = 'edited';
        $mock->enqueueJson(200, $fixture);

        $msg = $this->client($mock)->scheduled_messages->update('msg_xyz', ['text' => 'edited']);

        self::assertInstanceOf(Message::class, $msg);
        self::assertSame('edited', $msg->text);
        $req = $mock->requests()[0];
        self::assertSame('PATCH', $req->getMethod());
        self::assertSame(
            'https://api.blueticks.test/v1/scheduled-messages/msg_xyz',
            (string) $req->getUri(),
        );
        /** @var array<string, mixed> $decoded */
        $decoded = json_decode((string) $req->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(['text' => 'edited'], $decoded);
    }

    public function testUpdate401MapsToAuthenticationError(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(401, [
            'error' => [
                'code'       => 'authentication_required',
                'message'    => 'bad key',
                'request_id' => 'req_upd',
            ],
        ]);

        try {
            $this->client($mock)->scheduled_messages->update('msg_xyz', ['text' => 'x']);
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertSame(401, $e->statusCode);
            self::assertSame('req_upd', $e->requestId);
        }
    }
}
