<?php

declare(strict_types=1);

namespace Blueticks\Tests\Resources;

use Blueticks\Blueticks;
use Blueticks\Tests\Helpers\MockTransport;
use Blueticks\Types\Audience;
use Blueticks\Types\Contact;
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
            'contact_count' => 2,
            'created_at' => '2026-04-23T10:00:00Z',
        ];
    }

    /** @return array<string, mixed> */
    private static function contactFixture(): array
    {
        return [
            'id' => 'ct_1',
            'to' => '+15551234567',
            'variables' => ['name' => 'Alice'],
            'added_at' => '2026-04-23T10:00:00Z',
        ];
    }

    public function testCreate(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, self::audienceFixture());

        $a = $this->client($mock)->audiences->create('Customers', [
            ['to' => '+1', 'variables' => ['name' => 'A']],
        ]);
        self::assertInstanceOf(Audience::class, $a);

        $req = $mock->requests()[0];
        self::assertSame('POST', $req->getMethod());
        /** @var array<string, mixed> $body */
        $body = json_decode((string) $req->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('Customers', $body['name']);
        self::assertIsArray($body['contacts']);
        self::assertCount(1, $body['contacts']);
    }

    public function testList(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [self::audienceFixture()]);

        $list = $this->client($mock)->audiences->list();
        self::assertCount(1, $list);
    }

    public function testGetWithPage(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, self::audienceFixture());

        $this->client($mock)->audiences->get('aud_1', 2);
        $req = $mock->requests()[0];
        self::assertSame('GET', $req->getMethod());
        self::assertSame(
            'https://api.blueticks.test/v1/audiences/aud_1?page=2',
            (string) $req->getUri(),
        );
    }

    public function testGetWithoutPage(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, self::audienceFixture());

        $this->client($mock)->audiences->get('aud_1');
        self::assertSame(
            'https://api.blueticks.test/v1/audiences/aud_1',
            (string) $mock->requests()[0]->getUri(),
        );
    }

    public function testUpdate(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, self::audienceFixture());

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
        $mock->enqueueJson(200, []);

        $this->client($mock)->audiences->delete('aud_1');
        $req = $mock->requests()[0];
        self::assertSame('DELETE', $req->getMethod());
    }

    public function testAppendContacts(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['added' => 1, 'contact_count' => 3]);

        $r = $this->client($mock)->audiences->appendContacts('aud_1', [
            ['to' => '+1', 'variables' => ['x' => 'y']],
        ]);
        self::assertSame(1, $r->added);
        self::assertSame(3, $r->contactCount);

        $req = $mock->requests()[0];
        self::assertSame('POST', $req->getMethod());
        self::assertSame(
            'https://api.blueticks.test/v1/audiences/aud_1/contacts',
            (string) $req->getUri(),
        );
    }

    public function testUpdateContact(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, self::contactFixture());

        $c = $this->client($mock)->audiences->updateContact('aud_1', 'ct_1', [
            'variables' => ['name' => 'Bob'],
        ]);
        self::assertInstanceOf(Contact::class, $c);

        $req = $mock->requests()[0];
        self::assertSame('PATCH', $req->getMethod());
        self::assertSame(
            'https://api.blueticks.test/v1/audiences/aud_1/contacts/ct_1',
            (string) $req->getUri(),
        );
        /** @var array<string, mixed> $body */
        $body = json_decode((string) $req->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(['variables' => ['name' => 'Bob']], $body);
    }

    public function testDeleteContact(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, []);

        $this->client($mock)->audiences->deleteContact('aud_1', 'ct_1');
        $req = $mock->requests()[0];
        self::assertSame('DELETE', $req->getMethod());
        self::assertSame(
            'https://api.blueticks.test/v1/audiences/aud_1/contacts/ct_1',
            (string) $req->getUri(),
        );
    }
}
