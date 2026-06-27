<?php

declare(strict_types=1);

namespace Blueticks\Tests\Resources;

use Blueticks\Blueticks;
use Blueticks\Errors\AuthenticationError;
use Blueticks\Tests\Helpers\MockTransport;
use Blueticks\Types\Newsletter;
use Blueticks\Types\NewsletterListItem;
use Blueticks\Types\Page;
use PHPUnit\Framework\TestCase;

final class NewslettersResourceTest extends TestCase
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

    private function newsletterFixture(): array
    {
        return [
            'newsletterId' => '120363201733549020@newsletter',
            'name'         => 'My Channel',
            'description'  => 'Weekly updates',
            'createdAt'    => '2024-01-15T10:00:00Z',
            'subscribers'  => 42,
            'invite'       => 'abc123def456',
            'verification' => 'UNVERIFIED',
        ];
    }

    private function newsletterListFixture(): array
    {
        return [
            'chatId'       => '120363201733549020@newsletter',
            'name'         => 'My Channel',
            'description'  => 'Weekly updates',
            'createdAt'    => '2024-01-15T10:00:00Z',
            'subscribers'  => 42,
            'invite'       => 'abc123def456',
            'verification' => 'UNVERIFIED',
        ];
    }

    // --- list() ---

    public function testListReturnsPage(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'data'        => [$this->newsletterListFixture()],
            'hasMore'    => false,
            'nextCursor' => null,
        ]);

        $result = $this->client($mock)->newsletters->list();

        self::assertInstanceOf(Page::class, $result);
        self::assertCount(1, $result->data);
        self::assertInstanceOf(NewsletterListItem::class, $result->data[0]);
        self::assertSame('120363201733549020@newsletter', $result->data[0]->chatId);
        self::assertSame('My Channel', $result->data[0]->name);
        self::assertFalse($result->hasMore);
        self::assertNull($result->nextCursor);

        $req = $mock->requests()[0];
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.blueticks.test/v1/newsletters', (string) $req->getUri());
    }

    public function testList401MapsToAuthenticationError(): void
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
            $this->client($mock)->newsletters->list();
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertSame(401, $e->statusCode);
            self::assertSame('authentication_required', $e->code);
            self::assertSame('req_x', $e->requestId);
        }
    }

    // --- create() ---

    public function testCreateReturnsNewsletter(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(201, $this->newsletterFixture());

        $result = $this->client($mock)->newsletters->create([
            'name'        => 'My Channel',
            'description' => 'Weekly updates',
        ]);

        self::assertInstanceOf(Newsletter::class, $result);
        self::assertSame('120363201733549020@newsletter', $result->newsletterId);
        self::assertSame('My Channel', $result->name);
        self::assertSame('Weekly updates', $result->description);
        self::assertSame(42, $result->subscribers);
        self::assertSame('UNVERIFIED', $result->verification);

        $req = $mock->requests()[0];
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.blueticks.test/v1/newsletters', (string) $req->getUri());
        /** @var array<string, mixed> $body */
        $body = json_decode((string) $req->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('My Channel', $body['name']);
        self::assertSame('Weekly updates', $body['description']);
    }

    public function testCreate401MapsToAuthenticationError(): void
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
            $this->client($mock)->newsletters->create(['name' => 'Test']);
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertSame(401, $e->statusCode);
            self::assertSame('authentication_required', $e->code);
            self::assertSame('req_x', $e->requestId);
        }
    }

    // --- retrieve() ---

    public function testRetrieveReturnsNewsletter(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, $this->newsletterFixture());

        $result = $this->client($mock)->newsletters->retrieve('120363201733549020@newsletter');

        self::assertInstanceOf(Newsletter::class, $result);
        self::assertSame('120363201733549020@newsletter', $result->newsletterId);
        self::assertSame('My Channel', $result->name);

        $req = $mock->requests()[0];
        self::assertSame('GET', $req->getMethod());
        self::assertStringContainsString('/v1/newsletters/120363201733549020@newsletter', (string) $req->getUri());
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
            $this->client($mock)->newsletters->retrieve('120363201733549020@newsletter');
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertSame(401, $e->statusCode);
            self::assertSame('authentication_required', $e->code);
            self::assertSame('req_x', $e->requestId);
        }
    }
}
