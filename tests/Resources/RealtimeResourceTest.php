<?php

declare(strict_types=1);

namespace Blueticks\Tests\Resources;

use Blueticks\Blueticks;
use Blueticks\Errors\AuthenticationError;
use Blueticks\Tests\Helpers\MockTransport;
use Blueticks\Types\RealtimeTokenResponse;
use PHPUnit\Framework\TestCase;

final class RealtimeResourceTest extends TestCase
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

    public function testToken(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(201, ['success' => true, 'data' => [
            'brokerUrl' => 'wss://mqtt.blueticks.test/mqtt',
            'username' => 'u_1',
            'password' => 'secret',
            'expiresAt' => '2026-11-03T12:00:00Z',
            'expiresInSeconds' => 3600,
            'topics' => ['messages' => 'users/u_1/messages'],
        ]]);

        $t = $this->client($mock)->realtime->token();
        self::assertInstanceOf(RealtimeTokenResponse::class, $t);
        self::assertSame(3600, $t->expiresInSeconds);
        self::assertSame('users/u_1/messages', $t->topics->messages);
        $req = $mock->requests()[0];
        self::assertSame('POST', $req->getMethod());
        self::assertStringEndsWith('/v1/realtime/token', (string) $req->getUri());
    }

    public function testToken401(): void
    {
        $mock = new MockTransport();
        $this->unauthorized($mock);

        try {
            $this->client($mock)->realtime->token();
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertUnauthorized($e);
        }
    }
}
