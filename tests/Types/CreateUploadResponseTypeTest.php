<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\CreateUploadResponse;
use PHPUnit\Framework\TestCase;

final class CreateUploadResponseTypeTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function fixture(): array
    {
        return [
            'fileId' => 'file_abc',
            'uploadUrl' => 'https://storage.example.test/signed?sig=x',
            'method' => 'PUT',
            'headers' => ['Content-Type' => 'application/pdf', 'x-goog-content-length-range' => '0,25000000'],
            'expiresAt' => '2026-11-08T10:15:00Z',
            'deleteAt' => '2026-11-09T10:00:00Z',
            'maxBytes' => 25000000,
            'contentType' => 'application/pdf',
        ];
    }

    public function testHappyPath(): void
    {
        $u = CreateUploadResponse::fromArray(self::fixture());
        self::assertSame('file_abc', $u->fileId);
        self::assertSame('PUT', $u->method);
        self::assertSame('application/pdf', $u->headers['Content-Type']);
        self::assertSame(25000000.0, $u->maxBytes);
    }

    public function testMissingRequiredThrows(): void
    {
        $f = self::fixture();
        unset($f['uploadUrl']);
        $this->expectException(ValidationError::class);
        CreateUploadResponse::fromArray($f);
    }

    public function testWrongScalarThrows(): void
    {
        $f = self::fixture();
        $f['fileId'] = 123;
        $this->expectException(ValidationError::class);
        CreateUploadResponse::fromArray($f);
    }
}
