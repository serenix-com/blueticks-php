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

    public function test401MapsToAuthenticationError(): void
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
            $this->transport($mock)->request('GET', '/v1/ping');
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertSame(401, $e->statusCode);
            self::assertSame('authentication_required', $e->code);
            self::assertSame('bad key', $e->getMessage());
            self::assertSame('req_xyz', $e->requestId);
        }
    }

    public function test403MapsToPermissionDenied(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(403, [
            'error' => ['code' => 'permission_denied', 'message' => 'nope', 'request_id' => 'req_a'],
        ]);
        $this->expectException(PermissionDeniedError::class);
        $this->transport($mock)->request('GET', '/v1/ping');
    }

    public function test404MapsToNotFound(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(404, ['error' => ['code' => 'not_found', 'message' => 'missing', 'request_id' => 'req_b']]);
        $this->expectException(NotFoundError::class);
        $this->transport($mock)->request('GET', '/v1/ping');
    }

    public function test400MapsToBadRequest(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(400, ['error' => ['code' => 'invalid_request', 'message' => 'x', 'request_id' => 'req_c']]);
        $this->expectException(BadRequestError::class);
        $this->transport($mock)->request('GET', '/v1/ping');
    }

    public function test422MapsToBadRequest(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(422, ['error' => ['code' => 'invalid_request', 'message' => 'x', 'request_id' => 'req_d']]);
        $this->expectException(BadRequestError::class);
        $this->transport($mock)->request('GET', '/v1/ping');
    }

    public function test500MapsToAPIError(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(500, [
            'error' => ['code' => 'internal_error', 'message' => 'oops', 'request_id' => 'req_e'],
        ]);
        $this->expectException(APIError::class);
        $this->transport($mock, ['maxRetries' => 0])->request('GET', '/v1/ping');
    }

    public function testMalformedJsonBodyYieldsAPIErrorWithTruncatedMessage(): void
    {
        $mock = new MockTransport();
        $mock->enqueueRaw(500, str_repeat('x', 500), ['Content-Type' => 'text/plain']);

        try {
            $this->transport($mock, ['maxRetries' => 0])->request('GET', '/v1/ping');
            self::fail('Expected APIError');
        } catch (APIError $e) {
            self::assertSame(500, $e->statusCode);
            self::assertNull($e->code);
            self::assertSame(200, strlen($e->getMessage()));
        }
    }

    public function testNonEnvelopeJsonYieldsAPIErrorWithTruncatedBody(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(500, ['not' => 'an_envelope']);

        try {
            $this->transport($mock, ['maxRetries' => 0])->request('GET', '/v1/ping');
            self::fail('Expected APIError');
        } catch (APIError $e) {
            self::assertNull($e->code);
            self::assertStringContainsString('not', $e->getMessage());
        }
    }
}
