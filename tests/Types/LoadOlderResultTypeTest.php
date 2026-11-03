<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\LoadOlderResult;
use PHPUnit\Framework\TestCase;

final class LoadOlderResultTypeTest extends TestCase
{
    public function testHappyPath(): void
    {
        $r = LoadOlderResult::fromArray([
            'totalMessages' => 100,
            'added' => 5,
            'canLoadMore' => true,
            'historyUnavailable' => false,
            'pagesExecuted' => 2,
            'coverage' => ['oldestLoadedTimestamp' => 1700000000000, 'oldestLoadedIso' => '2023-11-14T22:13:20Z'],
        ]);
        self::assertSame(5, $r->added);
        self::assertTrue($r->canLoadMore);
        self::assertSame(2, $r->pagesExecuted);
        self::assertSame(1700000000000.0, $r->coverage->oldestLoadedTimestamp);
    }

    public function testMissingPagesExecutedThrows(): void
    {
        $this->expectException(ValidationError::class);
        LoadOlderResult::fromArray(['canLoadMore' => true, 'historyUnavailable' => false, 'coverage' => []]);
    }

    public function testMissingCoverageThrows(): void
    {
        $this->expectException(ValidationError::class);
        LoadOlderResult::fromArray(['canLoadMore' => true, 'historyUnavailable' => false, 'pagesExecuted' => 1]);
    }

    public function testMissingRequiredThrows(): void
    {
        $this->expectException(ValidationError::class);
        LoadOlderResult::fromArray(['canLoadMore' => true]);
    }

    public function testWrongScalarThrows(): void
    {
        $this->expectException(ValidationError::class);
        LoadOlderResult::fromArray(['canLoadMore' => 'yes', 'historyUnavailable' => false]);
    }
}
