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
        ]);
        self::assertSame(5, $r->added);
        self::assertTrue($r->canLoadMore);
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
