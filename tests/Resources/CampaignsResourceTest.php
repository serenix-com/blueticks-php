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
            'audience_id' => 'aud_1',
            'status' => 'pending',
            'total_count' => 0,
            'sent_count' => 0,
            'delivered_count' => 0,
            'read_count' => 0,
            'failed_count' => 0,
            'from' => null,
            'created_at' => '2026-04-23T10:00:00Z',
            'started_at' => null,
            'completed_at' => null,
            'aborted_at' => null,
        ];
    }

    public function testCreate(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, self::campaignFixture());

        $c = $this->client($mock)->campaigns->create('Spring', 'aud_1', [
            'text' => 'Hello {{name}}',
            'on_missing_variable' => 'skip',
        ]);
        self::assertInstanceOf(Campaign::class, $c);

        $req = $mock->requests()[0];
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.blueticks.test/v1/campaigns', (string) $req->getUri());
        /** @var array<string, mixed> $body */
        $body = json_decode((string) $req->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('Spring', $body['name']);
        self::assertSame('aud_1', $body['audience_id']);
        self::assertSame('Hello {{name}}', $body['text']);
        self::assertSame('skip', $body['on_missing_variable']);
    }

    public function testList(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'data' => [self::campaignFixture()],
            'has_more' => false,
            'next_cursor' => null,
        ]);

        $page = $this->client($mock)->campaigns->list();
        self::assertCount(1, $page->data);
        self::assertFalse($page->has_more);
    }

    public function testGet(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, self::campaignFixture());
        $this->client($mock)->campaigns->get('camp_1');
        self::assertSame(
            'https://api.blueticks.test/v1/campaigns/camp_1',
            (string) $mock->requests()[0]->getUri(),
        );
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
