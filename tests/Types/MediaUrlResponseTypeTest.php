<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\MediaUrlResponse;
use PHPUnit\Framework\TestCase;

final class MediaUrlResponseTypeTest extends TestCase
{
    public function testHappyPath(): void
    {
        $r = MediaUrlResponse::fromArray(['url' => 'https://cdn.example.com/x.jpg']);
        self::assertSame('https://cdn.example.com/x.jpg', $r->url);
    }

    public function testNullUrl(): void
    {
        $r = MediaUrlResponse::fromArray(['url' => null]);
        self::assertNull($r->url);
    }

    public function testMissingUrlThrows(): void
    {
        $this->expectException(ValidationError::class);
        MediaUrlResponse::fromArray([]);
    }

    public function testWrongTypeThrows(): void
    {
        $this->expectException(ValidationError::class);
        MediaUrlResponse::fromArray(['url' => 123]);
    }
}
