<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\GenerateSongResult;
use PHPUnit\Framework\TestCase;

final class GenerateSongResultTypeTest extends TestCase
{
    public function testHappyPath(): void
    {
        $r = GenerateSongResult::fromArray([
            'jobId' => 'job_1',
            'clips' => [
                ['id' => 'clip_1', 'status' => 'queued'],
                ['id' => 'clip_2', 'status' => 'submitted'],
            ],
        ]);
        self::assertSame('job_1', $r->jobId);
        self::assertCount(2, $r->clips);
        self::assertSame('clip_1', $r->clips[0]->id);
    }

    public function testMissingRequiredThrows(): void
    {
        $this->expectException(ValidationError::class);
        GenerateSongResult::fromArray(['jobId' => 'job_1']);
    }

    public function testWrongClipsTypeThrows(): void
    {
        $this->expectException(ValidationError::class);
        GenerateSongResult::fromArray(['jobId' => 'job_1', 'clips' => 'nope']);
    }
}
