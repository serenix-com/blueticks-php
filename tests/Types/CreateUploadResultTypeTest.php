<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\CreateUploadResult;
use PHPUnit\Framework\TestCase;

final class CreateUploadResultTypeTest extends TestCase
{
    public function testHappyPath(): void
    {
        $r = CreateUploadResult::fromArray(['uploadId' => 'up_1', 'status' => 'complete']);
        self::assertSame('up_1', $r->uploadId);
        self::assertSame('complete', $r->status);
    }

    public function testMissingRequiredThrows(): void
    {
        $this->expectException(ValidationError::class);
        CreateUploadResult::fromArray(['uploadId' => 'up_1']);
    }

    public function testWrongScalarThrows(): void
    {
        $this->expectException(ValidationError::class);
        CreateUploadResult::fromArray(['uploadId' => 1, 'status' => 'complete']);
    }
}
