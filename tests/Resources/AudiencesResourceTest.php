<?php

declare(strict_types=1);

namespace Blueticks\Tests\Resources;

use Blueticks\Blueticks;
use Blueticks\Errors\AuthenticationError;
use Blueticks\Tests\Helpers\MockTransport;
use Blueticks\Types\AppendContactsResult;
use Blueticks\Types\Audience;
use Blueticks\Types\AudienceContact;
use Blueticks\Types\DeletedResource;
use PHPUnit\Framework\TestCase;

final class AudiencesResourceTest extends TestCase
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
    private static function audienceFixture(): array
    {
        return [
            'id' => 'aud_1',
            'name' => 'Customers',
            'contactCount' => 2,
            'createdAt' => '2026-04-23T10:00:00Z',
        ];
    }

    public function testList(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'success' => true,
            'data' => [self::audienceFixture()],
            'limit' => 25,
            'skip' => 0,
            'total' => 1,
        ]);

        $page = $this->client($mock)->audiences->list(['limit' => 25]);
        self::assertCount(1, $page->data);
        self::assertSame(1, $page->total);
        self::assertInstanceOf(Audience::class, $page->data[0]);
    }

    public function testCreate(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => self::audienceFixture()]);

        $a = $this->client($mock)->audiences->create('Customers', [
            ['to' => '+1', 'variables' => ['name' => 'A']],
        ]);
        self::assertInstanceOf(Audience::class, $a);
        self::assertSame(2, $a->contactCount);

        $req = $mock->requests()[0];
        self::assertSame('POST', $req->getMethod());
        /** @var array<string, mixed> $body */
        $body = json_decode((string) $req->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('Customers', $body['name']);
    }

    public function testRetrieve(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => self::audienceFixture()]);

        $this->client($mock)->audiences->retrieve('aud_1');
        self::assertSame(
            'https://api.blueticks.test/v1/audiences/aud_1',
            (string) $mock->requests()[0]->getUri(),
        );
    }

    public function testUpdate(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => self::audienceFixture()]);

        $this->client($mock)->audiences->update('aud_1', 'Renamed');
        $req = $mock->requests()[0];
        self::assertSame('PATCH', $req->getMethod());
        /** @var array<string, mixed> $body */
        $body = json_decode((string) $req->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(['name' => 'Renamed'], $body);
    }

    public function testDelete(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => ['id' => 'aud_1', 'deleted' => true]]);

        $r = $this->client($mock)->audiences->delete('aud_1');
        self::assertInstanceOf(DeletedResource::class, $r);
        self::assertTrue($r->deleted);
        self::assertSame('DELETE', $mock->requests()[0]->getMethod());
    }

    public function testAppendContacts(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => ['added' => 1, 'contactCount' => 3]]);

        $r = $this->client($mock)->audiences->appendContacts('aud_1', [
            ['to' => '+1', 'variables' => ['x' => 'y']],
        ]);
        self::assertInstanceOf(AppendContactsResult::class, $r);
        self::assertSame(3, $r->contactCount);
    }

    public function testUpdateContact(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => [
            'id' => 'ct_1',
            'to' => '+15551234567',
            'variables' => ['name' => 'Bob'],
            'addedAt' => '2026-04-23T10:00:00Z',
        ]]);

        $c = $this->client($mock)->audiences->updateContact('aud_1', 'ct_1', [
            'variables' => ['name' => 'Bob'],
        ]);
        self::assertInstanceOf(AudienceContact::class, $c);
        self::assertSame('Bob', $c->variables['name']);
        self::assertSame(
            'https://api.blueticks.test/v1/audiences/aud_1/contacts/ct_1',
            (string) $mock->requests()[0]->getUri(),
        );
    }

    public function testDeleteContact(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => ['id' => 'ct_1']]);

        $this->client($mock)->audiences->deleteContact('aud_1', 'ct_1');
        $req = $mock->requests()[0];
        self::assertSame('DELETE', $req->getMethod());
        self::assertSame(
            'https://api.blueticks.test/v1/audiences/aud_1/contacts/ct_1',
            (string) $req->getUri(),
        );
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
            $this->client($mock)->audiences->retrieve('aud_1');
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertSame(401, $e->statusCode);
            self::assertSame('req_x', $e->requestId);
        }
    }
}
