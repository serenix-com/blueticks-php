<?php

declare(strict_types=1);

namespace Blueticks\Tests\Resources;

use Blueticks\Blueticks;
use Blueticks\Errors\AuthenticationError;
use Blueticks\Tests\Helpers\MockTransport;
use Blueticks\Types\DeletedResource;
use Blueticks\Types\Message;
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
            'id' => 'msg_1',
            'to' => '1@c.us',
            'type' => 'text',
            'text' => 'hi',
            'status' => 'pending',
            'createdAt' => '2026-04-23T10:00:00Z',
        ];
    }

    public function testList(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'success' => true,
            'data' => [self::messageFixture()],
            'limit' => 25,
            'skip' => 0,
            'total' => 1,
        ]);

        $page = $this->client($mock)->scheduled_messages->list(['status' => 'pending']);
        self::assertInstanceOf(Message::class, $page->data[0]);
        self::assertStringContainsString('status=pending', (string) $mock->requests()[0]->getUri());
    }

    public function testCreateWithChatIdInPath(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(201, ['success' => true, 'data' => self::messageFixture()]);

        $m = $this->client($mock)->scheduled_messages->create('1@c.us', [
            'type' => 'text',
            'text' => 'hi',
            'idempotency_key' => 'idem-9',
        ]);
        self::assertInstanceOf(Message::class, $m);

        $req = $mock->requests()[0];
        self::assertSame('POST', $req->getMethod());
        self::assertStringEndsWith('/v1/scheduled-messages/1%40c.us', (string) $req->getUri());
        self::assertSame('idem-9', $req->getHeaderLine('Idempotency-Key'));
    }

    public function testRetrieve(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => self::messageFixture()]);

        $m = $this->client($mock)->scheduled_messages->retrieve('msg_1');
        self::assertSame('msg_1', $m->id);
    }

    public function testUpdate(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => self::messageFixture()]);

        $this->client($mock)->scheduled_messages->update('msg_1', ['text' => 'edited']);
        self::assertSame('PATCH', $mock->requests()[0]->getMethod());
    }

    public function testDelete(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => ['id' => 'msg_1', 'deleted' => true]]);

        $r = $this->client($mock)->scheduled_messages->delete('msg_1');
        self::assertInstanceOf(DeletedResource::class, $r);
        self::assertSame('DELETE', $mock->requests()[0]->getMethod());
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
            $this->client($mock)->scheduled_messages->retrieve('msg_1');
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertSame('req_x', $e->requestId);
        }
    }

    public function testCancel(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => [
            'id' => 'msg_1',
            'to' => '1@c.us',
            'type' => 'text',
            'text' => 'hi',
            'status' => 'cancelled',
            'createdAt' => '2026-04-23T10:00:00Z',
        ]]);

        $m = $this->client($mock)->scheduled_messages->cancel('msg_1');
        self::assertInstanceOf(Message::class, $m);
        self::assertSame('cancelled', $m->status);
        self::assertSame('POST', $mock->requests()[0]->getMethod());
        self::assertSame('/v1/scheduled-messages/msg_1/cancel', $mock->requests()[0]->getUri()->getPath());
    }

    public function testCancel401(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(401, ['error' => [
            'code' => 'authentication_required',
            'message' => 'bad key',
            'request_id' => 'req_x',
        ]]);

        try {
            $this->client($mock)->scheduled_messages->cancel('msg_1');
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertSame(401, $e->statusCode);
            self::assertSame('authentication_required', $e->code);
            self::assertSame('req_x', $e->requestId);
        }
    }

    public function testListIncludeSentAndPreviewQuery(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => [], 'limit' => 25, 'skip' => 0, 'total' => 0]);

        $this->client($mock)->scheduled_messages->list(['includeSent' => true, 'includePreview' => false]);
        $uri = (string) $mock->requests()[0]->getUri();
        self::assertStringContainsString('includeSent=true', $uri);
        self::assertStringContainsString('includePreview=false', $uri);
    }

    public function testCreateWithoutTypeAndRecurrencePassesThrough(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => self::messageFixture()]);

        $recurrence = ['frequency' => 'daily', 'interval' => 1, 'endsAfter' => 5];
        $this->client($mock)->scheduled_messages->create('1@c.us', [
            'text' => 'hi',
            'sendAt' => '2026-12-01T09:00:00+02:00',
            'recurrence' => $recurrence,
            'waLabelIds' => ['lbl_1'],
        ]);
        $body = json_decode((string) $mock->requests()[0]->getBody(), true);
        self::assertArrayNotHasKey('type', $body);
        self::assertSame($recurrence, $body['recurrence']);
        self::assertSame(['lbl_1'], $body['waLabelIds']);
    }
}
