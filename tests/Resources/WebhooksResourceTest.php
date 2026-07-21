<?php

declare(strict_types=1);

namespace Blueticks\Tests\Resources;

use Blueticks\Blueticks;
use Blueticks\Errors\AuthenticationError;
use Blueticks\Tests\Helpers\MockTransport;
use Blueticks\Types\DeletedResource;
use Blueticks\Types\Webhook;
use PHPUnit\Framework\TestCase;

final class WebhooksResourceTest extends TestCase
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
    private static function webhookFixture(): array
    {
        return [
            'id' => 'wh_1',
            'url' => 'https://example.com/hook',
            'events' => ['message.delivered'],
            'description' => 'my hook',
            'status' => 'enabled',
            'createdAt' => '2026-04-23T10:00:00Z',
        ];
    }

    public function testList(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'success' => true,
            'data' => [self::webhookFixture()],
            'limit' => 25,
            'skip' => 0,
            'total' => 1,
        ]);

        $page = $this->client($mock)->webhooks->list();
        self::assertInstanceOf(Webhook::class, $page->data[0]);
        self::assertSame('enabled', $page->data[0]->status);
    }

    public function testCreateReturnsWebhook(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(201, ['success' => true, 'data' => self::webhookFixture()]);

        $wh = $this->client($mock)->webhooks->create(
            'https://example.com/hook',
            ['message.delivered'],
            'my hook',
        );
        self::assertInstanceOf(Webhook::class, $wh);
        self::assertSame('wh_1', $wh->id);
    }

    public function testRetrieve(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => self::webhookFixture()]);

        $wh = $this->client($mock)->webhooks->retrieve('wh_1');
        self::assertSame('wh_1', $wh->id);
    }

    public function testUpdate(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => self::webhookFixture()]);

        $this->client($mock)->webhooks->update('wh_1', ['status' => 'disabled']);
        $req = $mock->requests()[0];
        self::assertSame('PATCH', $req->getMethod());
        /** @var array<string, mixed> $body */
        $body = json_decode((string) $req->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(['status' => 'disabled'], $body);
    }

    public function testDelete(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => ['id' => 'wh_1', 'deleted' => true]]);

        $r = $this->client($mock)->webhooks->delete('wh_1');
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
            $this->client($mock)->webhooks->retrieve('wh_1');
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertSame('req_x', $e->requestId);
        }
    }
}
