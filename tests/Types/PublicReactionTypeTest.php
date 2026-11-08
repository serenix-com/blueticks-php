<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\PublicReaction;
use PHPUnit\Framework\TestCase;

final class PublicReactionTypeTest extends TestCase
{
    public function testHappyPath(): void
    {
        $r = PublicReaction::fromArray([
            'emoji' => "\u{1F44D}",
            'senderId' => '1@c.us',
            'fromMe' => true,
            'timestamp' => '2026-11-08T10:00:00Z',
        ]);
        self::assertSame("\u{1F44D}", $r->emoji);
        self::assertSame('1@c.us', $r->senderId);
        self::assertTrue($r->fromMe);
        self::assertSame('2026-11-08T10:00:00Z', $r->timestamp);
    }

    public function testMissingEmojiThrows(): void
    {
        $this->expectException(ValidationError::class);
        PublicReaction::fromArray(['senderId' => '1@c.us']);
    }

    public function testWrongFromMeTypeThrows(): void
    {
        $this->expectException(ValidationError::class);
        PublicReaction::fromArray(['emoji' => 'x', 'fromMe' => 'no']);
    }
}
