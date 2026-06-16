<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\ChatMedia;
use PHPUnit\Framework\TestCase;

final class ChatMediaTypeTest extends TestCase
{
    public function testHappyPathWithUrl(): void
    {
        $m = ChatMedia::fromArray([
            'url' => 'https://cdn.example.com/abc.jpg',
            'mimetype' => 'image/jpeg',
            'filename' => 'pic.jpg',
            'dataBase64' => null,
            'originalQuality' => null,
            'mediaUnavailable' => null,
        ]);
        self::assertSame('https://cdn.example.com/abc.jpg', $m->url);
        self::assertSame('image/jpeg', $m->mimetype);
        self::assertNull($m->originalQuality);
        self::assertNull($m->mediaUnavailable);
    }

    public function testOwnSentNewsletterPreviewQualityFlag(): void
    {
        $m = ChatMedia::fromArray([
            'url' => null,
            'mimetype' => 'image/jpeg',
            'filename' => null,
            'dataBase64' => '/9j/preview-bytes-here',
            'originalQuality' => false,
            'mediaUnavailable' => null,
        ]);
        self::assertFalse($m->originalQuality);
        self::assertSame('/9j/preview-bytes-here', $m->dataBase64);
    }

    public function testMediaUnavailableExpired(): void
    {
        $m = ChatMedia::fromArray([
            'url' => null,
            'mimetype' => null,
            'filename' => null,
            'dataBase64' => null,
            'originalQuality' => null,
            'mediaUnavailable' => 'expired',
        ]);
        self::assertSame('expired', $m->mediaUnavailable);
    }

    public function testMediaUnavailableAwaitingSender(): void
    {
        $m = ChatMedia::fromArray([
            'url' => null,
            'mimetype' => null,
            'filename' => null,
            'dataBase64' => null,
            'originalQuality' => null,
            'mediaUnavailable' => 'awaiting_sender',
        ]);
        self::assertSame('awaiting_sender', $m->mediaUnavailable);
    }

    public function testInvalidMediaUnavailableEnumThrows(): void
    {
        $this->expectException(ValidationError::class);
        ChatMedia::fromArray(['mediaUnavailable' => 'gone_forever']);
    }

    public function testWrongTypeOriginalQualityThrows(): void
    {
        $this->expectException(ValidationError::class);
        ChatMedia::fromArray(['originalQuality' => 'false']);
    }

    public function testEmptyArrayAllAbsent(): void
    {
        $m = ChatMedia::fromArray([]);
        self::assertNull($m->url);
        self::assertNull($m->originalQuality);
        self::assertNull($m->mediaUnavailable);
    }
}
