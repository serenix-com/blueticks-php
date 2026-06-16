<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\LoadOlderMessagesResponse;
use PHPUnit\Framework\TestCase;

final class LoadOlderMessagesResponseTypeTest extends TestCase
{
    public function testHappyPath(): void
    {
        $r = LoadOlderMessagesResponse::fromArray([
            'totalMessages' => 1200,
            'added' => 50,
            'canLoadMore' => true,
        ]);
        self::assertSame(1200, $r->totalMessages);
        self::assertSame(50, $r->added);
        self::assertTrue($r->canLoadMore);
    }

    public function testNullCounts(): void
    {
        $r = LoadOlderMessagesResponse::fromArray([
            'totalMessages' => null,
            'added' => null,
            'canLoadMore' => false,
        ]);
        self::assertNull($r->totalMessages);
        self::assertNull($r->added);
        self::assertFalse($r->canLoadMore);
    }

    public function testMissingCanLoadMoreThrows(): void
    {
        $this->expectException(ValidationError::class);
        LoadOlderMessagesResponse::fromArray([
            'totalMessages' => 0,
            'added' => 0,
        ]);
    }

    public function testWrongTypeAddedThrows(): void
    {
        $this->expectException(ValidationError::class);
        LoadOlderMessagesResponse::fromArray([
            'totalMessages' => 0,
            'added' => '50',
            'canLoadMore' => false,
        ]);
    }
}
