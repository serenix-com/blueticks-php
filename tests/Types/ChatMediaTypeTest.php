<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\ChatMedia;
use PHPUnit\Framework\TestCase;

final class ChatMediaTypeTest extends TestCase
{
    public function testHappyPath(): void
    {
        $m = ChatMedia::fromArray([
            'url' => 'https://x/y.jpg',
            'mimetype' => 'image/jpeg',
            'filename' => 'y.jpg',
            'dataBase64' => null,
            'originalQuality' => true,
        ]);
        self::assertSame('image/jpeg', $m->mimetype);
        self::assertTrue($m->originalQuality);
    }

    public function testUnavailableReason(): void
    {
        $m = ChatMedia::fromArray(['mediaUnavailable' => 'expired']);
        self::assertSame('expired', $m->mediaUnavailable);
    }

    public function testInvalidUnavailableEnumThrows(): void
    {
        $this->expectException(ValidationError::class);
        ChatMedia::fromArray(['mediaUnavailable' => 'gremlins']);
    }

    public function testWrongScalarThrows(): void
    {
        $this->expectException(ValidationError::class);
        ChatMedia::fromArray(['url' => 123]);
    }
}
