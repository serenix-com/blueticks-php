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
            'total_messages' => 1200,
            'added' => 50,
            'can_load_more' => true,
        ]);
        self::assertSame(1200, $r->total_messages);
        self::assertSame(50, $r->added);
        self::assertTrue($r->can_load_more);
    }

    public function testNullCounts(): void
    {
        $r = LoadOlderMessagesResponse::fromArray([
            'total_messages' => null,
            'added' => null,
            'can_load_more' => false,
        ]);
        self::assertNull($r->total_messages);
        self::assertNull($r->added);
        self::assertFalse($r->can_load_more);
    }

    public function testMissingCanLoadMoreThrows(): void
    {
        $this->expectException(ValidationError::class);
        LoadOlderMessagesResponse::fromArray([
            'total_messages' => 0,
            'added' => 0,
        ]);
    }

    public function testWrongTypeAddedThrows(): void
    {
        $this->expectException(ValidationError::class);
        LoadOlderMessagesResponse::fromArray([
            'total_messages' => 0,
            'added' => '50',
            'can_load_more' => false,
        ]);
    }
}
