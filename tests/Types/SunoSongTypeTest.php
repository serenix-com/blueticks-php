<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\SunoSong;
use PHPUnit\Framework\TestCase;

final class SunoSongTypeTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function fixture(): array
    {
        return [
            'id' => 'clip_a',
            'status' => 'complete',
            'audioUrl' => 'https://cdn.suno.example/clip_a.mp3',
            'imageUrl' => 'https://cdn.suno.example/clip_a.jpg',
            'title' => 'Morning Road',
            'durationSec' => 182.5,
            'model' => 'v5.5',
        ];
    }

    public function testHappyPath(): void
    {
        $s = SunoSong::fromArray(self::fixture());
        self::assertSame('clip_a', $s->id);
        self::assertSame('complete', $s->status);
        self::assertSame('https://cdn.suno.example/clip_a.mp3', $s->audioUrl);
        self::assertSame('https://cdn.suno.example/clip_a.jpg', $s->imageUrl);
        self::assertSame('Morning Road', $s->title);
        self::assertSame(182.5, $s->durationSec);
        self::assertSame('v5.5', $s->model);
        self::assertNull($s->errorType);
        self::assertNull($s->errorMessage);
    }

    public function testNullableAndErrorFields(): void
    {
        $f = self::fixture();
        $f['status'] = 'error';
        $f['audioUrl'] = null;
        $f['imageUrl'] = null;
        $f['title'] = null;
        $f['durationSec'] = null;
        $f['model'] = null;
        $f['errorType'] = 'moderation_failure';
        $f['errorMessage'] = 'Your lyrics contain copyrighted material';
        $s = SunoSong::fromArray($f);
        self::assertNull($s->audioUrl);
        self::assertNull($s->durationSec);
        self::assertSame('moderation_failure', $s->errorType);
        self::assertSame('Your lyrics contain copyrighted material', $s->errorMessage);
    }

    public function testMissingRequiredThrows(): void
    {
        $f = self::fixture();
        unset($f['id']);
        $this->expectException(ValidationError::class);
        SunoSong::fromArray($f);
    }

    public function testInvalidStatusEnumThrows(): void
    {
        $f = self::fixture();
        $f['status'] = 'done';
        $this->expectException(ValidationError::class);
        SunoSong::fromArray($f);
    }

    public function testWrongTypeDurationThrows(): void
    {
        $f = self::fixture();
        $f['durationSec'] = '182';
        $this->expectException(ValidationError::class);
        SunoSong::fromArray($f);
    }
}
