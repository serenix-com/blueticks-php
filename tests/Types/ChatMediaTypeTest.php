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
            'data_base64' => null,
            'original_quality' => null,
            'media_unavailable' => null,
        ]);
        self::assertSame('https://cdn.example.com/abc.jpg', $m->url);
        self::assertSame('image/jpeg', $m->mimetype);
        self::assertNull($m->original_quality);
        self::assertNull($m->media_unavailable);
    }

    public function testOwnSentNewsletterPreviewQualityFlag(): void
    {
        $m = ChatMedia::fromArray([
            'url' => null,
            'mimetype' => 'image/jpeg',
            'filename' => null,
            'data_base64' => '/9j/preview-bytes-here',
            'original_quality' => false,
            'media_unavailable' => null,
        ]);
        self::assertFalse($m->original_quality);
        self::assertSame('/9j/preview-bytes-here', $m->data_base64);
    }

    public function testMediaUnavailableExpired(): void
    {
        $m = ChatMedia::fromArray([
            'url' => null,
            'mimetype' => null,
            'filename' => null,
            'data_base64' => null,
            'original_quality' => null,
            'media_unavailable' => 'expired',
        ]);
        self::assertSame('expired', $m->media_unavailable);
    }

    public function testMediaUnavailableAwaitingSender(): void
    {
        $m = ChatMedia::fromArray([
            'url' => null,
            'mimetype' => null,
            'filename' => null,
            'data_base64' => null,
            'original_quality' => null,
            'media_unavailable' => 'awaiting_sender',
        ]);
        self::assertSame('awaiting_sender', $m->media_unavailable);
    }

    public function testInvalidMediaUnavailableEnumThrows(): void
    {
        $this->expectException(ValidationError::class);
        ChatMedia::fromArray(['media_unavailable' => 'gone_forever']);
    }

    public function testWrongTypeOriginalQualityThrows(): void
    {
        $this->expectException(ValidationError::class);
        ChatMedia::fromArray(['original_quality' => 'false']);
    }

    public function testEmptyArrayAllAbsent(): void
    {
        $m = ChatMedia::fromArray([]);
        self::assertNull($m->url);
        self::assertNull($m->original_quality);
        self::assertNull($m->media_unavailable);
    }
}
