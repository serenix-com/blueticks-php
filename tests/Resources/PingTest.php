<?php

declare(strict_types=1);

namespace Blueticks\Tests\Resources;

use Blueticks\Blueticks;
use Blueticks\Errors\AuthenticationError;
use Blueticks\Tests\Helpers\MockTransport;
use Blueticks\Types\Ping;
use PHPUnit\Framework\TestCase;

final class PingTest extends TestCase
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

    public function testPingHappyPath(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'account_id' => 'acct_abc123',
            'key_prefix' => 'bt_live_xy',
            'scopes' => ['messages:read'],
        ]);

        $result = $this->client($mock)->ping();

        self::assertInstanceOf(Ping::class, $result);
        self::assertSame('acct_abc123', $result->account_id);
        self::assertSame(['messages:read'], $result->scopes);
    }

    public function testPing401MapsToAuthenticationError(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(401, [
            'error' => [
                'code' => 'authentication_required',
                'message' => 'bad key',
                'request_id' => 'req_xyz',
            ],
        ]);

        try {
            $this->client($mock)->ping();
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertSame(401, $e->statusCode);
            self::assertSame('authentication_required', $e->code);
            self::assertSame('req_xyz', $e->requestId);
        }
    }
}
