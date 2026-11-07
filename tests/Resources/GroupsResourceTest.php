<?php

declare(strict_types=1);

namespace Blueticks\Tests\Resources;

use Blueticks\Blueticks;
use Blueticks\Errors\AuthenticationError;
use Blueticks\Tests\Helpers\MockTransport;
use Blueticks\Types\Group;
use PHPUnit\Framework\TestCase;

final class GroupsResourceTest extends TestCase
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
    private static function groupFixture(): array
    {
        return [
            'id' => '123@g.us',
            'name' => 'My Group',
            'participantCount' => 2,
            'participants' => [
                ['chatId' => '1@c.us', 'isAdmin' => true, 'isSuperAdmin' => true, 'name' => 'Owner'],
            ],
        ];
    }

    public function testList(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'success' => true,
            'data' => [['id' => '123@g.us', 'name' => 'My Group']],
            'limit' => 25,
            'skip' => 0,
            'total' => 1,
        ]);

        $page = $this->client($mock)->groups->list(['searchToken' => 'my']);
        self::assertInstanceOf(Group::class, $page->data[0]);
        self::assertNull($page->data[0]->participants);
    }

    public function testCreate(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => self::groupFixture()]);

        $g = $this->client($mock)->groups->create('My Group', ['1@c.us']);
        self::assertInstanceOf(Group::class, $g);
        self::assertCount(1, $g->participants ?? []);
    }

    public function testRetrieveWithInclude(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => self::groupFixture()]);

        $this->client($mock)->groups->retrieve('123@g.us', ['include' => 'participants']);
        self::assertStringContainsString('include=participants', (string) $mock->requests()[0]->getUri());
    }

    public function testUpdate(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => self::groupFixture()]);

        $this->client($mock)->groups->update('123@g.us', ['name' => 'X', 'settings' => ['announce' => true]]);
        $req = $mock->requests()[0];
        self::assertSame('PATCH', $req->getMethod());
        /** @var array<string, mixed> $body */
        $body = json_decode((string) $req->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('X', $body['name']);
    }

    public function testMemberMutations(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => self::groupFixture()]);
        $this->client($mock)->groups->addMember('123@g.us', '2@c.us');
        $req = $mock->requests()[0];
        self::assertSame('POST', $req->getMethod());
        self::assertStringEndsWith('/v1/groups/123%40g.us/members', (string) $req->getUri());

        $mock2 = new MockTransport();
        $mock2->enqueueJson(200, ['success' => true, 'data' => self::groupFixture()]);
        $this->client($mock2)->groups->promoteAdmin('123@g.us', '2@c.us');
        self::assertStringEndsWith('/members/2%40c.us/admin', (string) $mock2->requests()[0]->getUri());
    }

    public function testSetPicture(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => self::groupFixture()]);

        $this->client($mock)->groups->setPicture('123@g.us', ['fileDataUrl' => 'data:image/png;base64,AAA']);
        self::assertSame('PUT', $mock->requests()[0]->getMethod());
    }

    public function testGetPicture(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => ['url' => 'https://pps.whatsapp.net/x.jpg']]);

        $pic = $this->client($mock)->groups->getPicture('123@g.us');
        self::assertSame('GET', $mock->requests()[0]->getMethod());
        self::assertStringEndsWith('/v1/groups/123%40g.us/picture', (string) $mock->requests()[0]->getUri());
        self::assertSame('https://pps.whatsapp.net/x.jpg', $pic->url);
    }

    public function testGetPictureNullUrl(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => ['url' => null]]);

        self::assertNull($this->client($mock)->groups->getPicture('123@g.us')->url);
    }

    public function testRemovePicture(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => ['ok' => true]]);

        $res = $this->client($mock)->groups->removePicture('123@g.us');
        self::assertSame('DELETE', $mock->requests()[0]->getMethod());
        self::assertStringEndsWith('/v1/groups/123%40g.us/picture', (string) $mock->requests()[0]->getUri());
        self::assertTrue($res->ok);
    }

    public function testLeaveReturnsVoidOn204(): void
    {
        $mock = new MockTransport();
        $mock->enqueueRaw(204, '');

        $this->client($mock)->groups->leave('123@g.us');
        $req = $mock->requests()[0];
        self::assertSame('DELETE', $req->getMethod());
        self::assertStringEndsWith('/members/me', (string) $req->getUri());
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
            $this->client($mock)->groups->retrieve('123@g.us');
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertSame('req_x', $e->requestId);
        }
    }
}
