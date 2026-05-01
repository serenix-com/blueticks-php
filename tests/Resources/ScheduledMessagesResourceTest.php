<?php

declare(strict_types=1);

namespace Blueticks\Tests\Resources;

use Blueticks\Blueticks;
use Blueticks\Errors\AuthenticationError;
use Blueticks\Tests\Helpers\MockTransport;
use Blueticks\Types\ScheduledMessage;
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
    private static function fixture(): array
    {
        return [
            'id' => 'sched_507f1f77bcf86cd799439011',
            'to' => '+15551234567',
            'text' => 'Reminder',
            'media_url' => null,
            'media_caption' => null,
            'media_filename' => null,
            'media_mime_type' => null,
            'send_at' => '2026-05-15T09:00:00Z',
            'status' => 'scheduled',
            'is_recurring' => false,
            'recurrence_rule' => null,
            'created_at' => '2026-04-23T10:00:00Z',
            'updated_at' => '2026-04-24T11:00:00Z',
        ];
    }

    public function testList(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'data' => [self::fixture()],
            'has_more' => false,
            'next_cursor' => null,
        ]);

        $page = $this->client($mock)->scheduled_messages->list();
        self::assertCount(1, $page->data);
        self::assertInstanceOf(ScheduledMessage::class, $page->data[0]);
        self::assertSame('Reminder', $page->data[0]->text);

        $req = $mock->requests()[0];
        self::assertSame('GET', $req->getMethod());
        self::assertSame(
            'https://api.blueticks.test/v1/scheduled-messages',
            (string) $req->getUri(),
        );
    }

    public function testListUnauthorized(): void
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
            $this->client($mock)->scheduled_messages->list();
            self::fail('expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertSame(401, $e->statusCode);
            self::assertSame('authentication_required', $e->code);
            self::assertSame('req_x', $e->requestId);
        }
    }

    public function testRetrieve(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, self::fixture());

        $sm = $this->client($mock)->scheduled_messages->retrieve('sched_507f1f77bcf86cd799439011');
        self::assertInstanceOf(ScheduledMessage::class, $sm);
        self::assertSame('Reminder', $sm->text);

        $req = $mock->requests()[0];
        self::assertSame('GET', $req->getMethod());
        self::assertSame(
            'https://api.blueticks.test/v1/scheduled-messages/sched_507f1f77bcf86cd799439011',
            (string) $req->getUri(),
        );
    }

    public function testRetrieveUnauthorized(): void
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
            $this->client($mock)->scheduled_messages->retrieve('sched_1');
            self::fail('expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertSame(401, $e->statusCode);
            self::assertSame('authentication_required', $e->code);
            self::assertSame('req_x', $e->requestId);
        }
    }

    public function testUpdate(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, self::fixture());

        $sm = $this->client($mock)->scheduled_messages->update('sched_1', [
            'text' => 'Reminder',
            'send_at' => '2026-05-15T09:00:00Z',
        ]);
        self::assertInstanceOf(ScheduledMessage::class, $sm);

        $req = $mock->requests()[0];
        self::assertSame('PATCH', $req->getMethod());
        self::assertSame(
            'https://api.blueticks.test/v1/scheduled-messages/sched_1',
            (string) $req->getUri(),
        );
        /** @var array<string, mixed> $body */
        $body = json_decode((string) $req->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('Reminder', $body['text']);
        self::assertSame('2026-05-15T09:00:00Z', $body['send_at']);
    }

    public function testUpdateUnauthorized(): void
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
            $this->client($mock)->scheduled_messages->update('sched_1', ['text' => 'x']);
            self::fail('expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertSame(401, $e->statusCode);
            self::assertSame('authentication_required', $e->code);
            self::assertSame('req_x', $e->requestId);
        }
    }

    public function testDelete(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, self::fixture());

        $sm = $this->client($mock)->scheduled_messages->delete('sched_1');
        self::assertInstanceOf(ScheduledMessage::class, $sm);

        $req = $mock->requests()[0];
        self::assertSame('DELETE', $req->getMethod());
        self::assertSame(
            'https://api.blueticks.test/v1/scheduled-messages/sched_1',
            (string) $req->getUri(),
        );
    }

    public function testDeleteUnauthorized(): void
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
            $this->client($mock)->scheduled_messages->delete('sched_1');
            self::fail('expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertSame(401, $e->statusCode);
            self::assertSame('authentication_required', $e->code);
            self::assertSame('req_x', $e->requestId);
        }
    }
}
