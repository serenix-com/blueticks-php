<?php

declare(strict_types=1);

namespace Blueticks\Tests\Resources;

use Blueticks\Blueticks;
use Blueticks\Errors\AuthenticationError;
use Blueticks\Tests\Helpers\MockTransport;
use Blueticks\Types\Campaign;
use PHPUnit\Framework\TestCase;

final class CampaignsResourceTest extends TestCase
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
    private static function campaignFixture(): array
    {
        return [
            'cmpId' => 'cmp_1',
            'name' => 'Spring',
            'audienceId' => 'aud_1',
            'status' => 'running',
            'totalCount' => 10,
            'sentCount' => 4,
            'deliveredCount' => 3,
            'readCount' => 1,
            'failedCount' => 0,
            'createdAt' => '2026-04-23T10:00:00Z',
        ];
    }

    public function testList(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'success' => true,
            'data' => [self::campaignFixture()],
            'limit' => 25,
            'skip' => 0,
            'total' => 1,
        ]);

        $page = $this->client($mock)->campaigns->list();
        self::assertInstanceOf(Campaign::class, $page->data[0]);
        self::assertSame('running', $page->data[0]->status);
    }

    public function testCreate(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => self::campaignFixture()]);

        $c = $this->client($mock)->campaigns->create('Spring', 'aud_1', ['text' => 'Hi {firstName}']);
        self::assertInstanceOf(Campaign::class, $c);

        $req = $mock->requests()[0];
        self::assertSame('POST', $req->getMethod());
        /** @var array<string, mixed> $body */
        $body = json_decode((string) $req->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('aud_1', $body['audienceId']);
        self::assertSame('Hi {firstName}', $body['text']);
    }

    public function testRetrieve(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => self::campaignFixture()]);

        $c = $this->client($mock)->campaigns->retrieve('cmp_1');
        self::assertSame('cmp_1', $c->cmpId);
    }

    public function testPauseResumeCancel(): void
    {
        foreach (['pause', 'resume', 'cancel'] as $action) {
            $mock = new MockTransport();
            $mock->enqueueJson(200, ['success' => true, 'data' => self::campaignFixture()]);
            $this->client($mock)->campaigns->{$action}('cmp_1');
            $req = $mock->requests()[0];
            self::assertSame('POST', $req->getMethod());
            self::assertStringEndsWith("/v1/campaigns/cmp_1/{$action}", (string) $req->getUri());
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
            $this->client($mock)->campaigns->retrieve('cmp_1');
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertSame('req_x', $e->requestId);
        }
    }

    public function testCreatePassesSendAt(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => [
            'cmpId' => 'cmp_1',
            'name' => 'Promo',
            'audienceId' => 'aud_1',
            'status' => 'pending',
            'totalCount' => 0,
            'sentCount' => 0,
            'deliveredCount' => 0,
            'readCount' => 0,
            'failedCount' => 0,
            'createdAt' => '2026-04-23T10:00:00Z',
        ]]);

        $this->client($mock)->campaigns->create('Promo', 'aud_1', [
            'text' => 'hi',
            'sendAt' => '2026-12-01T09:00:00+02:00',
        ]);
        $body = json_decode((string) $mock->requests()[0]->getBody(), true);
        self::assertSame('2026-12-01T09:00:00+02:00', $body['sendAt']);
    }
}
