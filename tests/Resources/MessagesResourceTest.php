<?php

declare(strict_types=1);

namespace Blueticks\Tests\Resources;

use Blueticks\Blueticks;
use Blueticks\Errors\AuthenticationError;
use Blueticks\Tests\Helpers\MockTransport;
use Blueticks\Types\BatchMessageAck;
use Blueticks\Types\ChatMedia;
use Blueticks\Types\LoadOlderResult;
use Blueticks\Types\Message;
use Blueticks\Types\MessageAck;
use Blueticks\Types\OkResponse;
use Blueticks\Types\PinnedMessage;
use Blueticks\Types\PublicMessage;
use PHPUnit\Framework\TestCase;

final class MessagesResourceTest extends TestCase
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
    private static function publicMessageFixture(): array
    {
        return [
            'waMessageKey' => ['fromMe' => true, '_serialized' => 'true_1@c.us_ABC'],
            'chatId' => '1@c.us',
            'from' => '1@c.us',
            'type' => 'chat',
            'fromMe' => true,
            'text' => 'hello',
            'ack' => 3,
        ];
    }

    /** @return array<string, mixed> */
    private static function sendResultFixture(): array
    {
        return [
            'id' => 'true_1@c.us_ABC',
            'waMessageKey' => ['fromMe' => true, '_serialized' => 'true_1@c.us_ABC'],
            'to' => '1@c.us',
            'type' => 'text',
            'status' => 'sent',
            'createdAt' => '2026-04-23T10:00:00Z',
        ];
    }

    public function testList(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'success' => true,
            'data' => [self::publicMessageFixture()],
            'limit' => 25,
            'skip' => 0,
            'total' => 1,
        ]);

        $page = $this->client($mock)->messages->list([
            'chatId' => '1@c.us',
            'messageTypes' => ['chat', 'image'],
        ]);
        self::assertInstanceOf(PublicMessage::class, $page->data[0]);
        self::assertSame('true_1@c.us_ABC', $page->data[0]->waMessageKey->_serialized);
        self::assertStringContainsString('messageTypes=chat%2Cimage', (string) $mock->requests()[0]->getUri());
    }

    public function testRetrieve(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => self::publicMessageFixture()]);

        $m = $this->client($mock)->messages->retrieve('true_1@c.us_ABC', ['chatId' => '1@c.us']);
        self::assertInstanceOf(PublicMessage::class, $m);
        self::assertSame('hello', $m->text);
    }

    public function testCreateSendsWithIdempotencyKey(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(201, ['success' => true, 'data' => self::sendResultFixture()]);

        $m = $this->client($mock)->messages->create('1@c.us', [
            'type' => 'text',
            'text' => 'hi',
            'idempotency_key' => 'idem-1',
        ]);
        self::assertInstanceOf(Message::class, $m);

        $req = $mock->requests()[0];
        self::assertSame('POST', $req->getMethod());
        self::assertStringEndsWith('/v1/messages/1%40c.us', (string) $req->getUri());
        self::assertSame('idem-1', $req->getHeaderLine('Idempotency-Key'));
        /** @var array<string, mixed> $body */
        $body = json_decode((string) $req->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayNotHasKey('idempotency_key', $body);
    }

    public function testGetAck(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => ['ack' => 2]]);

        $ack = $this->client($mock)->messages->getAck('true_1@c.us_ABC');
        self::assertInstanceOf(MessageAck::class, $ack);
        self::assertSame(2, $ack->ack);
    }

    public function testBatchAcks(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'success' => true,
            'data' => [['key' => 'k1', 'ack' => 3, 'found' => true]],
        ]);

        $acks = $this->client($mock)->messages->batchAcks(['k1']);
        self::assertCount(1, $acks);
        self::assertInstanceOf(BatchMessageAck::class, $acks[0]);
        self::assertTrue($acks[0]->found);
    }

    public function testLoadOlder(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'success' => true,
            'data' => [
                'added' => 5,
                'canLoadMore' => true,
                'historyUnavailable' => false,
                'pagesExecuted' => 1,
                'coverage' => ['oldestLoadedTimestamp' => 1700000000000, 'oldestLoadedIso' => '2023-11-14T22:13:20Z'],
            ],
        ]);

        $r = $this->client($mock)->messages->loadOlder('1@c.us');
        self::assertInstanceOf(LoadOlderResult::class, $r);
        self::assertSame(5, $r->added);
        self::assertSame(1, $r->pagesExecuted);
        self::assertSame('2023-11-14T22:13:20Z', $r->coverage->oldestLoadedIso);
        self::assertSame('', (string) $mock->requests()[0]->getBody());
    }

    public function testLoadOlderWithPagesAndUntilDate(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'success' => true,
            'data' => [
                'canLoadMore' => false,
                'historyUnavailable' => false,
                'pagesExecuted' => 3,
                'coverage' => [],
            ],
        ]);

        $r = $this->client($mock)->messages->loadOlder('1@c.us', ['pages' => 3, 'until_date' => '2025-01-01']);
        self::assertSame(3, $r->pagesExecuted);
        self::assertNull($r->coverage->oldestLoadedTimestamp);
        self::assertSame('{"pages":3,"until_date":"2025-01-01"}', (string) $mock->requests()[0]->getBody());
    }

    public function testListSendsNewFilters(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'success' => true,
            'data' => [],
            'limit' => 25,
            'skip' => 0,
            'total' => 0,
        ]);

        $this->client($mock)->messages->list([
            'queryAny' => ['invoice', 'receipt'],
            'sender' => '1555',
            'hasMedia' => true,
            'mime' => 'application/pdf',
            'filenameContains' => 'q3',
        ]);
        $uri = (string) $mock->requests()[0]->getUri();
        self::assertStringContainsString('queryAny=invoice%2Creceipt', $uri);
        self::assertStringContainsString('sender=1555', $uri);
        self::assertStringContainsString('hasMedia=true', $uri);
        self::assertStringContainsString('mime=application%2Fpdf', $uri);
        self::assertStringContainsString('filenameContains=q3', $uri);
    }

    public function testGetMedia(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'success' => true,
            'data' => ['url' => 'https://x/y.jpg', 'mimetype' => 'image/jpeg'],
        ]);

        $media = $this->client($mock)->messages->getMedia('true_1@c.us_ABC', ['chatId' => '1@c.us']);
        self::assertInstanceOf(ChatMedia::class, $media);
        self::assertSame('image/jpeg', $media->mimetype);
    }

    public function testPinUnpinReact(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => ['ok' => true]]);
        $r = $this->client($mock)->messages->pin('k1', ['chatId' => '1@c.us', 'duration' => 3600]);
        self::assertInstanceOf(OkResponse::class, $r);
        /** @var array<string, mixed> $body */
        $body = json_decode((string) $mock->requests()[0]->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(3600, $body['duration']);

        $mock2 = new MockTransport();
        $mock2->enqueueJson(200, ['success' => true, 'data' => ['ok' => true]]);
        $this->client($mock2)->messages->react('k1', '👍', ['chatId' => '1@c.us']);
        /** @var array<string, mixed> $body2 */
        $body2 = json_decode((string) $mock2->requests()[0]->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('👍', $body2['emoji']);
    }

    public function testGetPinned(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'success' => true,
            'data' => [['key' => 'k1', 'chatId' => '1@c.us', 'text' => 'pinned']],
        ]);

        $pins = $this->client($mock)->messages->getPinned('1@c.us');
        self::assertCount(1, $pins);
        self::assertInstanceOf(PinnedMessage::class, $pins[0]);
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
            $this->client($mock)->messages->retrieve('k1');
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertSame('req_x', $e->requestId);
        }
    }

    public function testListEngineTargetingQuery(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => [], 'limit' => 25, 'skip' => 0, 'total' => 0]);

        $this->client($mock)->messages->list(['engine' => 'gateway', 'engineId' => 'eng_1', 'numberId' => 'num_1']);
        $uri = (string) $mock->requests()[0]->getUri();
        self::assertStringContainsString('engine=gateway', $uri);
        self::assertStringContainsString('engineId=eng_1', $uri);
        self::assertStringContainsString('numberId=num_1', $uri);
    }

    public function testListExposesCoverageAndAnsweredBy(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'success' => true,
            'data' => [],
            'limit' => 25,
            'skip' => 0,
            'total' => 0,
            'answeredBy' => ['engineId' => 'eng_1', 'numberId' => 'num_1', 'phoneNumber' => '972500000000'],
            'nextCursor' => '25',
            'hasMore' => true,
            'coverage' => ['messagesScanned' => 40, 'truncated' => false],
            'autoLoadedPages' => 0,
            'sinceReached' => true,
            'sources' => ['backup' => 3],
        ]);

        $page = $this->client($mock)->messages->list(['chatId' => '1@c.us']);
        self::assertNotNull($page->answeredBy);
        self::assertSame('eng_1', $page->answeredBy->engineId);
        self::assertSame('25', $page->nextCursor);
        self::assertTrue($page->hasMore);
        self::assertNotNull($page->coverage);
        self::assertSame(40, $page->coverage->messagesScanned);
        self::assertSame(0, $page->autoLoadedPages);
        self::assertTrue($page->sinceReached);
        self::assertSame(['backup' => 3], $page->sources);
    }

    public function testListPlainPageHasNullExtras(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => [], 'limit' => 25, 'skip' => 0, 'total' => 0]);

        $page = $this->client($mock)->messages->list();
        self::assertNull($page->answeredBy);
        self::assertNull($page->nextCursor);
        self::assertNull($page->coverage);
    }

    public function testCreatePassesWaLabelIdsAndMediaFileId(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => [
            'id' => 'msg_1',
            'to' => '1@c.us',
            'type' => 'media',
            'status' => 'sent',
            'createdAt' => '2026-04-23T10:00:00Z',
        ]]);

        $this->client($mock)->messages->create('1@c.us', [
            'type' => 'media',
            'mediaFileId' => 'file_1',
            'mediaMimeType' => 'application/pdf',
            'waLabelIds' => ['lbl_1'],
        ]);
        $body = json_decode((string) $mock->requests()[0]->getBody(), true);
        self::assertSame('file_1', $body['mediaFileId']);
        self::assertSame(['lbl_1'], $body['waLabelIds']);
    }
}
