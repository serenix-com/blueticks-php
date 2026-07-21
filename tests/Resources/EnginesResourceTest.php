<?php

declare(strict_types=1);

namespace Blueticks\Tests\Resources;

use Blueticks\Blueticks;
use Blueticks\Errors\AuthenticationError;
use Blueticks\Tests\Helpers\MockTransport;
use Blueticks\Types\Engine;
use PHPUnit\Framework\TestCase;

final class EnginesResourceTest extends TestCase
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

    public function testList(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'success' => true,
            'data' => [
                [
                    'id' => 'eng_1',
                    'type' => 'gateway',
                    'connected' => true,
                    'state' => 'CONNECTED',
                    'hasSynced' => true,
                ],
            ],
        ]);

        $engines = $this->client($mock)->engines->list();
        self::assertCount(1, $engines);
        self::assertInstanceOf(Engine::class, $engines[0]);
        self::assertSame('gateway', $engines[0]->type);
        self::assertTrue($engines[0]->connected);
    }

    public function testListEmpty(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['success' => true, 'data' => []]);

        self::assertSame([], $this->client($mock)->engines->list());
    }

    public function testList401(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(401, ['error' => [
            'code' => 'authentication_required',
            'message' => 'bad key',
            'request_id' => 'req_x',
        ]]);

        try {
            $this->client($mock)->engines->list();
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertSame('req_x', $e->requestId);
        }
    }
}
