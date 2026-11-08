<?php

declare(strict_types=1);

namespace Blueticks\Tests\Resources;

use Blueticks\Blueticks;
use Blueticks\Errors\AuthenticationError;
use Blueticks\Tests\Helpers\MockTransport;
use Blueticks\Types\Chat;
use Blueticks\Types\OkResponse;
use Blueticks\Types\Participant;
use PHPUnit\Framework\TestCase;

final class ChatsResourceTest extends TestCase
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
    private static function chatFixture(): array
    {
        return ['chatId' => '15551234567@c.us', 'name' => 'Alice', 'chatType' => 'contact', 'markedUnread' => false];
    }

    public function testList(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'success' => true,
            'data' => [self::chatFixture()],
            'limit' => 25,
            'skip' => 0,
            'total' => 1,
        ]);

        $page = $this->client($mock)->chats->list(['filter' => 'contacts', 'includeArchive' => true]);
        self::assertInstanceOf(Chat::class, $page->data[0]);
        self::assertSame('contact', $page->data[0]->chatType);

        $uri = (string) $mock->requests()[0]->getUri();
        self::assertStringContainsString('filter=contacts', $uri);
        self::assertStringContainsString('includeArchive=true', $uri);
    }

    public function testRetrieve(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => self::chatFixture()]);

        $c = $this->client($mock)->chats->retrieve('15551234567@c.us');
        self::assertSame('15551234567@c.us', $c->chatId);
    }

    public function testListParticipants(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'success' => true,
            'data' => [['chatId' => '1@c.us', 'name' => 'A', 'isAdmin' => true, 'isSuperAdmin' => false]],
            'limit' => 25,
            'skip' => 0,
            'total' => 1,
        ]);

        $page = $this->client($mock)->chats->listParticipants('123@g.us');
        self::assertInstanceOf(Participant::class, $page->data[0]);
        self::assertTrue($page->data[0]->isAdmin);
    }

    public function testArchiveUnarchiveMarkRead(): void
    {
        foreach (['archive', 'unarchive', 'markRead'] as $method) {
            $mock = new MockTransport();
            $mock->enqueueJson(200, ['success' => true, 'data' => ['ok' => true]]);
            $r = $this->client($mock)->chats->{$method}('15551234567@c.us');
            self::assertInstanceOf(OkResponse::class, $r);
            self::assertSame('POST', $mock->requests()[0]->getMethod());
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
            $this->client($mock)->chats->retrieve('15551234567@c.us');
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertSame('req_x', $e->requestId);
        }
    }

    public function testListSendsKindsAndSince(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => [], 'limit' => 50, 'skip' => 0, 'total' => 0]);

        $this->client($mock)->chats->list(['kinds' => ['contact', 'group'], 'since' => '2026-11-01T00:00:00+02:00']);
        $uri = (string) $mock->requests()[0]->getUri();
        self::assertStringContainsString('kinds=contact%2Cgroup', $uri);
        self::assertStringContainsString('since=2026-11-01T00%3A00%3A00%2B02%3A00', $uri);
    }

    public function testListEngineTargetingQuery(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => [], 'limit' => 25, 'skip' => 0, 'total' => 0]);

        $this->client($mock)->chats->list([
            'engine' => 'gateway',
            'engineId' => 'eng_1',
            'numberId' => 'num_1',
            'phone' => '972500000000',
            'fanoutLimit' => 3,
        ]);
        $uri = (string) $mock->requests()[0]->getUri();
        self::assertStringContainsString('engine=gateway', $uri);
        self::assertStringContainsString('engineId=eng_1', $uri);
        self::assertStringContainsString('numberId=num_1', $uri);
        self::assertStringContainsString('phone=972500000000', $uri);
        self::assertStringContainsString('fanoutLimit=3', $uri);
    }

    public function testListParticipantsEngineTargetingQuery(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => [], 'limit' => 25, 'skip' => 0, 'total' => 0]);

        $this->client($mock)->chats->listParticipants('123@g.us', ['engine' => 'regular', 'fanoutLimit' => 2]);
        $uri = (string) $mock->requests()[0]->getUri();
        self::assertStringContainsString('engine=regular', $uri);
        self::assertStringContainsString('fanoutLimit=2', $uri);
    }

    public function testListWaLabels(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'success' => true,
            'data' => [['id' => 'lbl_1', 'name' => 'VIP', 'color' => '#ff0000']],
        ]);

        $labels = $this->client($mock)->chats->listWaLabels('15551234567@c.us');
        self::assertCount(1, $labels);
        self::assertInstanceOf(\Blueticks\Types\WaLabel::class, $labels[0]);
        self::assertSame('lbl_1', $labels[0]->id);
        self::assertSame('GET', $mock->requests()[0]->getMethod());
        self::assertStringContainsString(
            '/v1/chats/15551234567%40c.us/wa-labels',
            (string) $mock->requests()[0]->getUri(),
        );
    }

    public function testUpdateWaLabels(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => ['ok' => true]]);

        $r = $this->client($mock)->chats->updateWaLabels('15551234567@c.us', ['lbl_1', 'lbl_2']);
        self::assertInstanceOf(OkResponse::class, $r);
        self::assertTrue($r->ok);
        self::assertSame('PATCH', $mock->requests()[0]->getMethod());
        self::assertStringContainsString(
            '/v1/chats/15551234567%40c.us/wa-labels',
            (string) $mock->requests()[0]->getUri(),
        );
        $body = json_decode((string) $mock->requests()[0]->getBody(), true);
        self::assertSame(['labelIds' => ['lbl_1', 'lbl_2']], $body);
    }

    public function testListWaLabels401(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(401, ['error' => [
            'code' => 'authentication_required',
            'message' => 'bad key',
            'request_id' => 'req_x',
        ]]);

        try {
            $this->client($mock)->chats->listWaLabels('15551234567@c.us');
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertSame(401, $e->statusCode);
            self::assertSame('req_x', $e->requestId);
        }
    }
}
