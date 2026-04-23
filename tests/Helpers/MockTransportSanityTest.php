<?php

declare(strict_types=1);

namespace Blueticks\Tests\Helpers;

use PHPUnit\Framework\TestCase;

final class MockTransportSanityTest extends TestCase
{
    public function testEnqueueAndDispatch(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['hello' => 'world']);

        $request = $mock->factories()->createRequest('GET', 'https://example.test/');
        $response = $mock->client()->sendRequest($request);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('{"hello":"world"}', (string) $response->getBody());
        self::assertCount(1, $mock->requests());
    }
}
