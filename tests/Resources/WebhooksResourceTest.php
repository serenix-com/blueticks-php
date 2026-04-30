<?php

declare(strict_types=1);

namespace Blueticks\Tests\Resources;

use Blueticks\Blueticks;
use Blueticks\Tests\Helpers\MockTransport;
use Blueticks\Types\Webhook;
use Blueticks\Types\WebhookCreateResult;
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
            'url' => 'https://example.com/webhooks',
            'events' => ['message.delivered'],
            'description' => null,
            'status' => 'enabled',
            'created_at' => '2026-04-23T10:00:00Z',
        ];
    }

    public function testCreateReturnsSecret(): void
    {
        $mock = new MockTransport();
        $f = self::webhookFixture();
        $f['secret'] = 'whsec_abc';
        $mock->enqueueJson(200, $f);

        $w = $this->client($mock)->webhooks->create(
            'https://example.com/webhooks',
            ['message.delivered'],
            'my hook',
        );
        self::assertInstanceOf(WebhookCreateResult::class, $w);
        self::assertSame('whsec_abc', $w->secret);

        $req = $mock->requests()[0];
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.blueticks.test/v1/webhooks', (string) $req->getUri());
        /** @var array<string, mixed> $body */
        $body = json_decode((string) $req->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('https://example.com/webhooks', $body['url']);
        self::assertSame(['message.delivered'], $body['events']);
        self::assertSame('my hook', $body['description']);
    }

    public function testList(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'data' => [self::webhookFixture(), self::webhookFixture()],
            'has_more' => false,
            'next_cursor' => null,
        ]);

        $page = $this->client($mock)->webhooks->list();
        self::assertCount(2, $page->data);
        self::assertInstanceOf(Webhook::class, $page->data[0]);
        self::assertFalse($page->has_more);
        self::assertNull($page->next_cursor);

        $req = $mock->requests()[0];
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.blueticks.test/v1/webhooks', (string) $req->getUri());
    }

    public function testGet(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, self::webhookFixture());

        $w = $this->client($mock)->webhooks->get('wh_1');
        self::assertSame('wh_1', $w->id);
        self::assertSame(
            'https://api.blueticks.test/v1/webhooks/wh_1',
            (string) $mock->requests()[0]->getUri(),
        );
    }

    public function testUpdateSendsPatch(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, self::webhookFixture());

        $this->client($mock)->webhooks->update('wh_1', [
            'url' => 'https://new.example.com/hooks',
            'status' => 'disabled',
        ]);
        $req = $mock->requests()[0];
        self::assertSame('PATCH', $req->getMethod());
        /** @var array<string, mixed> $body */
        $body = json_decode((string) $req->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('https://new.example.com/hooks', $body['url']);
        self::assertSame('disabled', $body['status']);
        self::assertArrayNotHasKey('events', $body);
    }

    public function testDelete(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, []);

        $this->client($mock)->webhooks->delete('wh_1');

        $req = $mock->requests()[0];
        self::assertSame('DELETE', $req->getMethod());
        self::assertSame('https://api.blueticks.test/v1/webhooks/wh_1', (string) $req->getUri());
    }

    public function testRotateSecret(): void
    {
        $mock = new MockTransport();
        $f = self::webhookFixture();
        $f['secret'] = 'whsec_new';
        $mock->enqueueJson(200, $f);

        $r = $this->client($mock)->webhooks->rotateSecret('wh_1');
        self::assertSame('whsec_new', $r->secret);
        $req = $mock->requests()[0];
        self::assertSame('POST', $req->getMethod());
        self::assertSame(
            'https://api.blueticks.test/v1/webhooks/wh_1/rotate-secret',
            (string) $req->getUri(),
        );
    }
}
