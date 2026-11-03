<?php

declare(strict_types=1);

namespace Blueticks\Tests\Resources;

use Blueticks\Blueticks;
use Blueticks\Errors\AuthenticationError;
use Blueticks\Tests\Helpers\MockTransport;
use Blueticks\Types\Identity;
use PHPUnit\Framework\TestCase;

final class IdentityResourceTest extends TestCase
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
    private static function identityFixture(): array
    {
        return [
            'input' => '123@lid',
            'resolved' => true,
            'canonical' => '15551234567@c.us',
            'phone' => '+15551234567',
            'lid' => '123@lid',
            'aliases' => ['123@lid'],
            'type' => 'user',
            'source' => 'engine',
        ];
    }

    public function testResolve(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => [
            self::identityFixture(),
            ['input' => 'x@lid', 'resolved' => false, 'canonical' => null, 'phone' => null, 'lid' => null,
                'aliases' => [], 'type' => 'unknown', 'source' => 'unknown'],
        ]]);

        $r = $this->client($mock)->identity->resolve(['123@lid', 'x@lid']);
        self::assertCount(2, $r);
        self::assertInstanceOf(Identity::class, $r[0]);
        self::assertSame('+15551234567', $r[0]->phone);
        self::assertFalse($r[1]->resolved);
        self::assertNull($r[1]->canonical);
        self::assertSame('{"ids":["123@lid","x@lid"]}', (string) $mock->requests()[0]->getBody());
    }

    public function testResolve401(): void
    {
        $mock = new MockTransport();
        $this->unauthorized($mock);

        try {
            $this->client($mock)->identity->resolve(['123@lid']);
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertUnauthorized($e);
        }
    }

    public function testRetrieve(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => self::identityFixture()]);

        $r = $this->client($mock)->identity->retrieve('123@lid');
        self::assertSame('user', $r->type);
        self::assertStringEndsWith('/v1/identity/123%40lid', (string) $mock->requests()[0]->getUri());
    }

    public function testRetrieve401(): void
    {
        $mock = new MockTransport();
        $this->unauthorized($mock);

        try {
            $this->client($mock)->identity->retrieve('123@lid');
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertUnauthorized($e);
        }
    }
}
