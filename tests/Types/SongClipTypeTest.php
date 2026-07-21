<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\SongClip;
use PHPUnit\Framework\TestCase;

final class SongClipTypeTest extends TestCase
{
    public function testHappyPath(): void
    {
        $c = SongClip::fromArray([
            'id' => 'clip_1',
            'status' => 'complete',
            'audioUrl' => 'https://x/a.mp3',
            'durationSec' => 183.4,
        ]);
        self::assertSame('clip_1', $c->id);
        self::assertSame('complete', $c->status);
        self::assertSame(183.4, $c->durationSec);
    }

    public function testMissingRequiredThrows(): void
    {
        $this->expectException(ValidationError::class);
        SongClip::fromArray(['id' => 'clip_1']);
    }

    public function testInvalidStatusEnumThrows(): void
    {
        $this->expectException(ValidationError::class);
        SongClip::fromArray(['id' => 'clip_1', 'status' => 'vibing']);
    }
}
