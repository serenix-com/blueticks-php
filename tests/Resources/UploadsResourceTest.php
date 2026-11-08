<?php

declare(strict_types=1);

namespace Blueticks\Tests\Resources;

use Blueticks\Blueticks;
use Blueticks\Errors\AuthenticationError;
use Blueticks\Tests\Helpers\MockTransport;
use Blueticks\Types\CreateUploadResponse;
use PHPUnit\Framework\TestCase;

final class UploadsResourceTest extends TestCase
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

    public function testCreate(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(201, [
            'success' => true,
            'data' => [
                'fileId' => 'file_1',
                'uploadUrl' => 'https://storage.example.test/signed',
                'method' => 'PUT',
                'headers' => ['Content-Type' => 'application/pdf'],
                'expiresAt' => '2026-11-08T10:15:00Z',
                'deleteAt' => '2026-11-09T10:00:00Z',
                'maxBytes' => 25000000,
                'contentType' => 'application/pdf',
            ],
        ]);

        $u = $this->client($mock)->uploads->create([
            'contentType' => 'application/pdf',
            'sizeBytes' => 482113,
            'filename' => 'invoice.pdf',
        ]);
        self::assertInstanceOf(CreateUploadResponse::class, $u);
        self::assertSame('file_1', $u->fileId);
        self::assertSame('POST', $mock->requests()[0]->getMethod());
        self::assertSame('/v1/uploads', $mock->requests()[0]->getUri()->getPath());
        $body = json_decode((string) $mock->requests()[0]->getBody(), true);
        self::assertSame(
            [
                'contentType' => 'application/pdf',
                'sizeBytes' => 482113,
                'filename' => 'invoice.pdf',
            ],
            $body,
        );
    }

    public function testCreate401(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(401, ['error' => [
            'code' => 'authentication_required',
            'message' => 'bad key',
            'request_id' => 'req_x',
        ]]);

        try {
            $this->client($mock)->uploads->create(['contentType' => 'image/png']);
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertSame(401, $e->statusCode);
            self::assertSame('authentication_required', $e->code);
            self::assertSame('req_x', $e->requestId);
        }
    }
}
