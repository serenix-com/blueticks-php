<?php

declare(strict_types=1);

namespace Blueticks\Tests\Resources;

use Blueticks\Blueticks;
use Blueticks\Tests\Helpers\MockTransport;
use Blueticks\Types\Message;
use PHPUnit\Framework\TestCase;

final class MessagesResourceTest extends TestCase
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
            'to' => '+15551234567',
            'from' => null,
            'text' => 'hello',
            'media_url' => null,
            'status' => 'queued',
            'send_at' => null,
            'created_at' => '2026-04-23T10:00:00Z',
            'sent_at' => null,
            'delivered_at' => null,
            'read_at' => null,
            'failed_at' => null,
            'failure_reason' => null,
        ];
    }

    public function testSendBasic(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, self::messageFixture());

        $msg = $this->client($mock)->messages->send('+15551234567', ['text' => 'hello']);

        self::assertInstanceOf(Message::class, $msg);
        self::assertSame('msg_1', $msg->id);

        $req = $mock->requests()[0];
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.blueticks.test/v1/messages', (string) $req->getUri());
        $body = (string) $req->getBody();
        /** @var array<string, mixed> $decoded */
        $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(['to' => '+15551234567', 'text' => 'hello'], $decoded);
    }

    public function testSendWithAllOptions(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, self::messageFixture());

        $this->client($mock)->messages->send('+15551234567', [
            'text' => 'hi',
            'media_url' => 'https://cdn.example.com/x.jpg',
            'media_caption' => 'pic',
            'send_at' => '2026-04-24T09:00:00Z',
            'from' => 'sess_1',
        ]);

        $body = (string) $mock->requests()[0]->getBody();
        /** @var array<string, mixed> $decoded */
        $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('hi', $decoded['text']);
        self::assertSame('https://cdn.example.com/x.jpg', $decoded['media_url']);
        self::assertSame('pic', $decoded['media_caption']);
        self::assertSame('2026-04-24T09:00:00Z', $decoded['send_at']);
        self::assertSame('sess_1', $decoded['from']);
    }

    public function testSendPropagatesIdempotencyKey(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, self::messageFixture());

        $this->client($mock)->messages->send('+15551234567', [
            'text' => 'hi',
            'idempotency_key' => 'key_abc',
        ]);

        $req = $mock->requests()[0];
        self::assertSame('key_abc', $req->getHeaderLine('Idempotency-Key'));
        $body = (string) $req->getBody();
        /** @var array<string, mixed> $decoded */
        $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayNotHasKey('idempotency_key', $decoded);
    }

    public function testGetById(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, self::messageFixture());

        $msg = $this->client($mock)->messages->get('msg_1');

        self::assertSame('msg_1', $msg->id);
        $req = $mock->requests()[0];
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.blueticks.test/v1/messages/msg_1', (string) $req->getUri());
    }
}
