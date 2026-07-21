<?php

declare(strict_types=1);

namespace Blueticks\Tests\Resources;

use Blueticks\Blueticks;
use Blueticks\Errors\AuthenticationError;
use Blueticks\Tests\Helpers\MockTransport;
use Blueticks\Types\Newsletter;
use Blueticks\Types\NewsletterListItem;
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

    public function testList(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'success' => true,
            'data' => [['chatId' => '120@newsletter', 'name' => 'News', 'subscribers' => 10]],
            'limit' => 25,
            'skip' => 0,
            'total' => 1,
        ]);

        $page = $this->client($mock)->newsletters->list();
        self::assertInstanceOf(NewsletterListItem::class, $page->data[0]);
        self::assertSame('120@newsletter', $page->data[0]->chatId);
    }

    public function testCreate(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(201, [
            'success' => true,
            'data' => ['newsletterId' => '120@newsletter', 'name' => 'News'],
        ]);

        $n = $this->client($mock)->newsletters->create(['name' => 'News']);
        self::assertInstanceOf(Newsletter::class, $n);
        self::assertSame('120@newsletter', $n->newsletterId);
        self::assertSame('POST', $mock->requests()[0]->getMethod());
    }

    public function testRetrieve(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'success' => true,
            'data' => ['newsletterId' => '120@newsletter', 'name' => 'News', 'verification' => 'VERIFIED'],
        ]);

        $n = $this->client($mock)->newsletters->retrieve('120@newsletter');
        self::assertSame('VERIFIED', $n->verification);
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
            $this->client($mock)->newsletters->retrieve('120@newsletter');
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertSame('req_x', $e->requestId);
        }
    }
}
