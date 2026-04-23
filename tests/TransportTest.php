<?php

declare(strict_types=1);

namespace Blueticks\Tests;

use Blueticks\Errors\APIConnectionError;
use Blueticks\Errors\APIError;
use Blueticks\Errors\AuthenticationError;
use Blueticks\Errors\BadRequestError;
use Blueticks\Errors\NotFoundError;
use Blueticks\Errors\PermissionDeniedError;
use Blueticks\Errors\RateLimitError;
use Blueticks\Tests\Helpers\MockTransport;
use Blueticks\Transport;
use Http\Client\Exception\NetworkException;
use PHPUnit\Framework\TestCase;

final class TransportTest extends TestCase
{
    /** @param array<string, mixed> $overrides */
    private function transport(MockTransport $mock, array $overrides = []): Transport
    {
        return new Transport(array_merge([
            'apiKey'         => 'bt_test_fake',
            'baseUrl'        => 'https://api.blueticks.test',
            'timeout'        => 1.0,
            'maxRetries'     => 2,
            'userAgent'      => null,
            'httpClient'     => $mock->client(),
            'requestFactory' => $mock->factories(),
            'streamFactory'  => $mock->factories(),
            'retryBaseMs'    => 0,
            'retryCapMs'     => 0,
            'sleeper'        => function (int $_ms): void {
            },
        ], $overrides));
    }

    public function testGetReturnsDecodedBody(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['ok' => true, 'val' => 42]);

        $result = $this->transport($mock)->request('GET', '/v1/ping');

        self::assertSame(['ok' => true, 'val' => 42], $result);
    }

    public function testHeadersIncludeAuthAndUserAgent(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, []);

        $this->transport($mock)->request('GET', '/v1/ping');

        $request = $mock->requests()[0];
        self::assertSame('Bearer bt_test_fake', $request->getHeaderLine('Authorization'));
        self::assertSame('application/json', $request->getHeaderLine('Accept'));
        self::assertStringStartsWith('blueticks-php/', $request->getHeaderLine('User-Agent'));
    }

    public function testUserAgentSuffixIsAppended(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, []);

        $this->transport($mock, ['userAgent' => 'MyApp/7.2'])->request('GET', '/v1/ping');

        $ua = $mock->requests()[0]->getHeaderLine('User-Agent');
        self::assertMatchesRegularExpression('#^blueticks-php/\S+ MyApp/7\.2$#', $ua);
    }

    public function testBodyIsJsonEncodedAndContentTypeSet(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['id' => 'x']);

        $this->transport($mock)->request('POST', '/v1/things', [
            'body' => ['name' => 'alice', 'age' => 30],
            'headers' => ['Idempotency-Key' => 'k_123'],
        ]);

        $req = $mock->requests()[0];
        self::assertSame('POST', $req->getMethod());
        self::assertSame('application/json', $req->getHeaderLine('Content-Type'));
        self::assertSame('{"name":"alice","age":30}', (string) $req->getBody());
        self::assertSame('k_123', $req->getHeaderLine('Idempotency-Key'));
    }

    public function testBaseUrlTrailingSlashNormalization(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, []);
        $this->transport($mock, ['baseUrl' => 'https://api.blueticks.test/'])
            ->request('GET', '/v1/ping');
        self::assertSame('https://api.blueticks.test/v1/ping', (string) $mock->requests()[0]->getUri());
    }
}
