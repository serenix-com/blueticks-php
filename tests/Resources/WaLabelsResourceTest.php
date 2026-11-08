<?php

declare(strict_types=1);

namespace Blueticks\Tests\Resources;

use Blueticks\Blueticks;
use Blueticks\Errors\AuthenticationError;
use Blueticks\Tests\Helpers\MockTransport;
use Blueticks\Types\WaLabel;
use PHPUnit\Framework\TestCase;

final class WaLabelsResourceTest extends TestCase
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
            'data' => [['id' => 'lbl_1', 'name' => 'VIP', 'color' => '#ff0000'], ['id' => 'lbl_2']],
        ]);

        $labels = $this->client($mock)->wa_labels->list();
        self::assertCount(2, $labels);
        self::assertInstanceOf(WaLabel::class, $labels[0]);
        self::assertSame('VIP', $labels[0]->name);
        self::assertNull($labels[1]->name);
        self::assertSame('GET', $mock->requests()[0]->getMethod());
        self::assertSame('/v1/wa-labels', $mock->requests()[0]->getUri()->getPath());
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
            $this->client($mock)->wa_labels->list();
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertSame(401, $e->statusCode);
            self::assertSame('authentication_required', $e->code);
            self::assertSame('req_x', $e->requestId);
        }
    }
}
