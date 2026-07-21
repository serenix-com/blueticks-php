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

    public function testRetrieveViaResource(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => [
            'api' => 'ok',
            'accountId' => 'acc_1',
            'whatsappConnections' => [
                ['id' => 'eng_1', 'type' => 'gateway', 'connected' => true],
            ],
        ]]);

        $ping = $this->client($mock)->ping();
        self::assertInstanceOf(Ping::class, $ping);
        self::assertSame('acc_1', $ping->accountId);
        self::assertCount(1, $ping->whatsappConnections);
        self::assertSame('gateway', $ping->whatsappConnections[0]->type);
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
            $this->client($mock)->ping();
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertSame('req_x', $e->requestId);
        }
    }
}
