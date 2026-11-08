<?php

declare(strict_types=1);

namespace Blueticks\Tests\Resources;

use Blueticks\Blueticks;
use Blueticks\Errors\AuthenticationError;
use Blueticks\Tests\Helpers\MockTransport;
use Blueticks\Types\Contact;
use Blueticks\Types\Group;
use PHPUnit\Framework\TestCase;

final class ContactsResourceTest extends TestCase
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
            'data' => [['chatId' => '1@c.us', 'name' => 'Alice', 'isBusiness' => false]],
            'limit' => 25,
            'skip' => 0,
            'total' => 1,
        ]);

        $page = $this->client($mock)->contacts->list(['searchToken' => 'ali']);
        self::assertInstanceOf(Contact::class, $page->data[0]);
        self::assertSame('Alice', $page->data[0]->name);
        self::assertStringContainsString('searchToken=ali', (string) $mock->requests()[0]->getUri());
    }

    public function testCommonGroups(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'success' => true,
            'data' => [['id' => '123@g.us', 'name' => 'Shared']],
        ]);

        $groups = $this->client($mock)->contacts->commonGroups('1@c.us');
        self::assertCount(1, $groups);
        self::assertInstanceOf(Group::class, $groups[0]);
        self::assertStringEndsWith('/v1/contacts/1%40c.us/common_groups', (string) $mock->requests()[0]->getUri());
    }

    public function testList401(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(401, ['error' => [
            'code' => 'authentication_required',
            'message' => 'bad key',
            'request_id' => 'req_x',
        ]]);

        try {
            $this->client($mock)->contacts->list();
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertSame('req_x', $e->requestId);
        }
    }

    public function testCommonGroupsLimitAndSkipQuery(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => []]);

        $this->client($mock)->contacts->commonGroups('1@c.us', ['limit' => 10, 'skip' => 5]);
        $uri = (string) $mock->requests()[0]->getUri();
        self::assertStringContainsString('limit=10', $uri);
        self::assertStringContainsString('skip=5', $uri);
    }
}
