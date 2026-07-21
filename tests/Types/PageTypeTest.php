<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\Page;
use PHPUnit\Framework\TestCase;

final class PageTypeTest extends TestCase
{
    public function testHappyPath(): void
    {
        $page = Page::fromArray(
            [
                'success' => true,
                'data' => [['id' => 'a'], ['id' => 'b']],
                'limit' => 25,
                'skip' => 50,
                'total' => 120,
            ],
            fn (array $row): object => (object) $row,
        );
        self::assertCount(2, $page->data);
        self::assertSame(25, $page->limit);
        self::assertSame(50, $page->skip);
        self::assertSame(120, $page->total);
    }

    public function testMissingDataThrows(): void
    {
        $this->expectException(ValidationError::class);
        Page::fromArray(
            ['limit' => 25, 'skip' => 0, 'total' => 0],
            fn (array $row): object => (object) $row,
        );
    }

    public function testMissingTotalThrows(): void
    {
        $this->expectException(ValidationError::class);
        Page::fromArray(
            ['data' => [], 'limit' => 25, 'skip' => 0],
            fn (array $row): object => (object) $row,
        );
    }
}
