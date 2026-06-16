<?php

declare(strict_types=1);

namespace Blueticks\Tests\Resources;

use Blueticks\Blueticks;
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
            'id' => 'camp_1',
            'name' => 'Spring',
            'audienceId' => 'aud_1',
            'status' => 'pending',
            'totalCount' => 0,
            'sentCount' => 0,
            'deliveredCount' => 0,
            'readCount' => 0,
            'failedCount' => 0,
            'from' => null,
            'createdAt' => '2026-04-23T10:00:00Z',
            'startedAt' => null,
            'completedAt' => null,
            'abortedAt' => null,
        ];
    }

    public function testCreate(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, self::campaignFixture());

        $c = $this->client($mock)->campaigns->create('Spring', 'aud_1', [
            'text' => 'Hello {{name}}',
            'onMissingVariable' => 'skip',
        ]);
        self::assertInstanceOf(Campaign::class, $c);

        $req = $mock->requests()[0];
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.blueticks.test/v1/campaigns', (string) $req->getUri());
        /** @var array<string, mixed> $body */
        $body = json_decode((string) $req->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('Spring', $body['name']);
        self::assertSame('aud_1', $body['audienceId']);
        self::assertSame('Hello {{name}}', $body['text']);
        self::assertSame('skip', $body['onMissingVariable']);
    }

    public function testList(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'data' => [self::campaignFixture()],
            'hasMore' => false,
            'nextCursor' => null,
        ]);

        $page = $this->client($mock)->campaigns->list();
        self::assertCount(1, $page->data);
        self::assertFalse($page->hasMore);
    }

    public function testRetrieve(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, self::campaignFixture());
        $this->client($mock)->campaigns->retrieve('camp_1');
        self::assertSame(
            'https://api.blueticks.test/v1/campaigns/camp_1',
            (string) $mock->requests()[0]->getUri(),
        );
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
            $this->client($mock)->campaigns->retrieve('camp_1');
            self::fail('Expected AuthenticationError');
        } catch (\Blueticks\Errors\AuthenticationError $e) {
            self::assertSame(401, $e->statusCode);
            self::assertSame('authentication_required', $e->code);
            self::assertSame('req_x', $e->requestId);
        }
    }

    public function testPauseResumeCancel(): void
    {
        foreach (['pause', 'resume', 'cancel'] as $verb) {
            $mock = new MockTransport();
            $mock->enqueueJson(200, self::campaignFixture());
            $this->client($mock)->campaigns->{$verb}('camp_1');
            $req = $mock->requests()[0];
            self::assertSame('POST', $req->getMethod(), "verb={$verb}");
            self::assertSame(
                "https://api.blueticks.test/v1/campaigns/camp_1/{$verb}",
                (string) $req->getUri(),
                "verb={$verb}",
            );
        }
    }
}
