<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\SunoGeneration;
use Blueticks\Types\SunoSong;
use PHPUnit\Framework\TestCase;

final class SunoGenerationTypeTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function clip(string $id): array
    {
        return [
            'id' => $id,
            'status' => 'submitted',
            'audioUrl' => null,
            'imageUrl' => null,
            'title' => null,
            'durationSec' => null,
            'model' => 'v5.5',
        ];
    }

    /** @return array<string, mixed> */
    private static function fixture(): array
    {
        return [
            'jobId' => 'job_1',
            'clips' => [self::clip('clip_a'), self::clip('clip_b')],
        ];
    }

    public function testHappyPath(): void
    {
        $g = SunoGeneration::fromArray(self::fixture());
        self::assertSame('job_1', $g->jobId);
        self::assertCount(2, $g->clips);
        self::assertInstanceOf(SunoSong::class, $g->clips[0]);
        self::assertSame('clip_a', $g->clips[0]->id);
        self::assertSame('clip_b', $g->clips[1]->id);
    }

    public function testMissingRequiredThrows(): void
    {
        $f = self::fixture();
        unset($f['jobId']);
        $this->expectException(ValidationError::class);
        SunoGeneration::fromArray($f);
    }

    public function testWrongTypeClipsThrows(): void
    {
        $f = self::fixture();
        $f['clips'] = 'not-an-array';
        $this->expectException(ValidationError::class);
        SunoGeneration::fromArray($f);
    }
}
