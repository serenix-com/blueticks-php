<?php

declare(strict_types=1);

namespace Blueticks\Tests\Resources;

use Blueticks\Blueticks;
use Blueticks\Errors\AuthenticationError;
use Blueticks\Tests\Helpers\MockTransport;
use Blueticks\Types\BoardDetail;
use Blueticks\Types\BoardItem;
use Blueticks\Types\BoardRow;
use Blueticks\Types\BulkItemsResponse;
use Blueticks\Types\FindSimilarResponse;
use PHPUnit\Framework\TestCase;

final class BoardsResourceTest extends TestCase
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

    private function unauthorized(MockTransport $mock): void
    {
        $mock->enqueueJson(401, ['error' => [
            'code' => 'authentication_required',
            'message' => 'bad key',
            'request_id' => 'req_x',
        ]]);
    }

    private static function assertUnauthorized(AuthenticationError $e): void
    {
        self::assertSame(401, $e->statusCode);
        self::assertSame('authentication_required', $e->code);
        self::assertSame('req_x', $e->requestId);
    }

    /** @return array<string, mixed> */
    private static function boardFixture(): array
    {
        return [
            'board_id' => 'brd_1',
            'name' => 'Tasks',
            'domain' => 'tasks',
            'agent_managed' => true,
            'web_url' => 'https://app.blueticks.test/boards/brd_1',
            'created_at' => '2026-11-01T10:00:00Z',
            'updated_at' => '2026-11-02T10:00:00Z',
        ];
    }

    /** @return array<string, mixed> */
    private static function detailFixture(): array
    {
        return self::boardFixture() + [
            'columns' => [
                ['column_id' => 'c1', 'title' => 'Status', 'type' => 'status', 'options' => [
                    ['label' => 'Done', 'bgcolor' => '#0f0'],
                ]],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private static function itemFixture(): array
    {
        return [
            'item_id' => 'it_1',
            'text' => 'Call Dana',
            'status' => ['label' => 'Open', 'bgcolor' => '#00f'],
            'priority' => null,
            'web_url' => 'https://app.blueticks.test/boards/brd_1/it_1',
            'created_at' => '2026-11-01T10:00:00Z',
            'updated_at' => '2026-11-02T10:00:00Z',
        ];
    }

    public function testList(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'success' => true,
            'data' => [self::boardFixture()],
            'limit' => 50,
            'skip' => 0,
            'total' => 1,
        ]);

        $page = $this->client($mock)->boards->list(['order' => 'asc', 'limit' => 10]);
        self::assertInstanceOf(BoardRow::class, $page->data[0]);
        self::assertSame('Tasks', $page->data[0]->name);
        $uri = (string) $mock->requests()[0]->getUri();
        self::assertStringContainsString('/v1/boards?', $uri);
        self::assertStringContainsString('order=asc', $uri);
    }

    public function testList401(): void
    {
        $mock = new MockTransport();
        $this->unauthorized($mock);

        try {
            $this->client($mock)->boards->list();
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertUnauthorized($e);
        }
    }

    public function testCreate(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => self::detailFixture()]);

        $b = $this->client($mock)->boards->create(['domain' => 'tasks']);
        self::assertInstanceOf(BoardDetail::class, $b);
        self::assertSame('Done', $b->columns[0]->options[0]->label);
        $req = $mock->requests()[0];
        self::assertSame('POST', $req->getMethod());
        self::assertSame('{"domain":"tasks"}', (string) $req->getBody());
    }

    public function testCreate401(): void
    {
        $mock = new MockTransport();
        $this->unauthorized($mock);

        try {
            $this->client($mock)->boards->create([]);
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertUnauthorized($e);
        }
    }

    public function testRetrieve(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => self::detailFixture()]);

        $b = $this->client($mock)->boards->retrieve('brd_1');
        self::assertSame('brd_1', $b->board_id);
        self::assertStringEndsWith('/v1/boards/brd_1', (string) $mock->requests()[0]->getUri());
    }

    public function testRetrieve401(): void
    {
        $mock = new MockTransport();
        $this->unauthorized($mock);

        try {
            $this->client($mock)->boards->retrieve('brd_1');
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertUnauthorized($e);
        }
    }

    public function testListItems(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'success' => true,
            'data' => [self::itemFixture()],
            'limit' => 50,
            'skip' => 0,
            'total' => 1,
        ]);

        $page = $this->client($mock)->boards->listItems('brd_1', ['active_only' => true]);
        self::assertInstanceOf(BoardItem::class, $page->data[0]);
        self::assertNull($page->data[0]->priority);
        self::assertNotNull($page->data[0]->status);
        self::assertSame('Open', $page->data[0]->status->label);
        $uri = (string) $mock->requests()[0]->getUri();
        self::assertStringContainsString('/v1/boards/brd_1/items?', $uri);
        self::assertStringContainsString('active_only=true', $uri);
    }

    public function testListItems401(): void
    {
        $mock = new MockTransport();
        $this->unauthorized($mock);

        try {
            $this->client($mock)->boards->listItems('brd_1');
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertUnauthorized($e);
        }
    }

    public function testCreateItem(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(201, ['success' => true, 'data' => self::itemFixture()]);

        $i = $this->client($mock)->boards->createItem('brd_1', ['text' => 'Call Dana', 'status' => 'Open']);
        self::assertSame('it_1', $i->item_id);
        $req = $mock->requests()[0];
        self::assertSame('POST', $req->getMethod());
        self::assertStringEndsWith('/v1/boards/brd_1/items', (string) $req->getUri());
    }

    public function testCreateItem401(): void
    {
        $mock = new MockTransport();
        $this->unauthorized($mock);

        try {
            $this->client($mock)->boards->createItem('brd_1', ['text' => 'x']);
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertUnauthorized($e);
        }
    }

    public function testUpdateItem(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => self::itemFixture()]);

        $i = $this->client($mock)->boards->updateItem('brd_1', 'it_1', ['deleted' => true]);
        self::assertSame('Call Dana', $i->text);
        $req = $mock->requests()[0];
        self::assertSame('PATCH', $req->getMethod());
        self::assertStringEndsWith('/v1/boards/brd_1/items/it_1', (string) $req->getUri());
    }

    public function testUpdateItem401(): void
    {
        $mock = new MockTransport();
        $this->unauthorized($mock);

        try {
            $this->client($mock)->boards->updateItem('brd_1', 'it_1', []);
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertUnauthorized($e);
        }
    }

    public function testBulkCreateItems(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => [
            'created' => [self::itemFixture()],
            'updated' => [],
            'unchanged' => 2,
            'board' => ['board_id' => 'brd_1', 'name' => 'Tasks', 'web_url' => 'https://app.blueticks.test/b'],
        ]]);

        $r = $this->client($mock)->boards->bulkCreateItems('brd_1', ['items' => [['text' => 'a']]]);
        self::assertInstanceOf(BulkItemsResponse::class, $r);
        self::assertCount(1, $r->created);
        self::assertSame(2, $r->unchanged);
        self::assertSame('brd_1', $r->board->board_id);
        self::assertStringEndsWith('/v1/boards/brd_1/items/bulk', (string) $mock->requests()[0]->getUri());
    }

    public function testBulkCreateItems401(): void
    {
        $mock = new MockTransport();
        $this->unauthorized($mock);

        try {
            $this->client($mock)->boards->bulkCreateItems('brd_1', ['items' => []]);
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertUnauthorized($e);
        }
    }

    public function testFindSimilar(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => [
            'matches' => [['candidate_index' => 0, 'item_id' => 'it_1', 'text' => 'Call Dana', 'score' => 0.9]],
            'misses' => [1],
        ]]);

        $r = $this->client($mock)->boards->findSimilar('brd_1', ['candidates' => [['text' => 'a'], ['text' => 'b']]]);
        self::assertInstanceOf(FindSimilarResponse::class, $r);
        self::assertSame('it_1', $r->matches[0]->item_id);
        self::assertSame([1], $r->misses);
        self::assertStringEndsWith('/v1/boards/brd_1/items/find-similar', (string) $mock->requests()[0]->getUri());
    }

    public function testFindSimilar401(): void
    {
        $mock = new MockTransport();
        $this->unauthorized($mock);

        try {
            $this->client($mock)->boards->findSimilar('brd_1', ['candidates' => []]);
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertUnauthorized($e);
        }
    }
}
