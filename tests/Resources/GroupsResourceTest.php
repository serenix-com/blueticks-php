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
            'id' => '1234567890-9876543210@g.us',
            'name' => 'Acme Team',
            'description' => 'Internal coordination',
            'owner' => '1234567890@c.us',
            'created_at' => '2026-04-23T10:00:00Z',
            'last_message_at' => '2026-04-30T10:00:00Z',
            'participant_count' => 3,
            'announce' => false,
            'restrict' => false,
            'participants' => [
                [
                    'chat_id' => '1234567890@c.us',
                    'is_admin' => true,
                    'is_super_admin' => true,
                    'name' => 'Owner',
                ],
                [
                    'chat_id' => '5550000001@c.us',
                    'is_admin' => false,
                    'is_super_admin' => false,
                    'name' => 'Alice',
                ],
                [
                    'chat_id' => '5550000002@c.us',
                    'is_admin' => false,
                    'is_super_admin' => false,
                    'name' => null,
                ],
            ],
        ];
    }

    public function testCreate(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, self::groupFixture());

        $g = $this->client($mock)->groups->create('Acme Team', [
            '1234567890@c.us',
            '5550000001@c.us',
        ]);

        self::assertInstanceOf(Group::class, $g);
        self::assertSame('Acme Team', $g->name);

        $req = $mock->requests()[0];
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.blueticks.test/v1/groups', (string) $req->getUri());
        /** @var array<string, mixed> $body */
        $body = json_decode((string) $req->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('Acme Team', $body['name']);
        self::assertSame(['1234567890@c.us', '5550000001@c.us'], $body['participants']);
    }

    public function testGet401MapsToAuthenticationError(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(401, [
            'error' => [
                'code' => 'authentication_required',
                'message' => 'bad key',
                'request_id' => 'req_x',
            ],
        ]);

        try {
            $this->client($mock)->groups->get('1234567890-9876543210@g.us');
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertSame(401, $e->statusCode);
            self::assertSame('authentication_required', $e->code);
            self::assertSame('req_x', $e->requestId);
        }
    }

    public function testGet(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, self::groupFixture());

        $g = $this->client($mock)->groups->get('1234567890-9876543210@g.us');
        self::assertInstanceOf(Group::class, $g);
        self::assertSame(3, $g->participant_count);
    }

    public function testUpdate(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, self::groupFixture());

        $this->client($mock)->groups->update('1234567890-9876543210@g.us', [
            'name' => 'Renamed',
            'settings' => ['announce' => true, 'restrict' => false],
        ]);

        $req = $mock->requests()[0];
        self::assertSame('PATCH', $req->getMethod());
        /** @var array<string, mixed> $body */
        $body = json_decode((string) $req->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('Renamed', $body['name']);
        self::assertSame(['announce' => true, 'restrict' => false], $body['settings']);
    }

    public function testAddMember(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, self::groupFixture());

        $g = $this->client($mock)->groups->addMember(
            '1234567890-9876543210@g.us',
            '5550000003@c.us',
        );
        self::assertInstanceOf(Group::class, $g);

        $req = $mock->requests()[0];
        self::assertSame('POST', $req->getMethod());
        self::assertSame(
            'https://api.blueticks.test/v1/groups/1234567890-9876543210%40g.us/members',
            (string) $req->getUri(),
        );
        /** @var array<string, mixed> $body */
        $body = json_decode((string) $req->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(['chat_id' => '5550000003@c.us'], $body);
    }

    public function testRemoveMember(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, self::groupFixture());

        $g = $this->client($mock)->groups->removeMember(
            '1234567890-9876543210@g.us',
            '5550000001@c.us',
        );
        self::assertInstanceOf(Group::class, $g);

        $req = $mock->requests()[0];
        self::assertSame('DELETE', $req->getMethod());
        self::assertSame(
            'https://api.blueticks.test/v1/groups/1234567890-9876543210%40g.us/members/5550000001%40c.us',
            (string) $req->getUri(),
        );
    }

    public function testPromoteAdmin(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, self::groupFixture());

        $g = $this->client($mock)->groups->promoteAdmin(
            '1234567890-9876543210@g.us',
            '5550000001@c.us',
        );
        self::assertInstanceOf(Group::class, $g);

        $req = $mock->requests()[0];
        self::assertSame('POST', $req->getMethod());
        self::assertSame(
            'https://api.blueticks.test/v1/groups/1234567890-9876543210%40g.us/members/5550000001%40c.us/admin',
            (string) $req->getUri(),
        );
    }

    public function testDemoteAdmin(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, self::groupFixture());

        $g = $this->client($mock)->groups->demoteAdmin(
            '1234567890-9876543210@g.us',
            '5550000001@c.us',
        );
        self::assertInstanceOf(Group::class, $g);

        $req = $mock->requests()[0];
        self::assertSame('DELETE', $req->getMethod());
        self::assertSame(
            'https://api.blueticks.test/v1/groups/1234567890-9876543210%40g.us/members/5550000001%40c.us/admin',
            (string) $req->getUri(),
        );
    }

    public function testSetPicture(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, self::groupFixture());

        $g = $this->client($mock)->groups->setPicture('1234567890-9876543210@g.us', [
            'file_data_url' => 'data:image/png;base64,iVBORw0KGgo=',
            'file_name' => 'logo.png',
            'file_mime_type' => 'image/png',
        ]);
        self::assertInstanceOf(Group::class, $g);

        $req = $mock->requests()[0];
        self::assertSame('PUT', $req->getMethod());
        self::assertSame(
            'https://api.blueticks.test/v1/groups/1234567890-9876543210%40g.us/picture',
            (string) $req->getUri(),
        );
        /** @var array<string, mixed> $body */
        $body = json_decode((string) $req->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('data:image/png;base64,iVBORw0KGgo=', $body['file_data_url']);
        self::assertSame('logo.png', $body['file_name']);
        self::assertSame('image/png', $body['file_mime_type']);
    }

    public function testLeaveReturnsVoid(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(204, []);

        $this->client($mock)->groups->leave('1234567890-9876543210@g.us');

        $req = $mock->requests()[0];
        self::assertSame('DELETE', $req->getMethod());
        self::assertSame(
            'https://api.blueticks.test/v1/groups/1234567890-9876543210%40g.us/members/me',
            (string) $req->getUri(),
        );
    }
}
