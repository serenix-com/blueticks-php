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

    public function testOptionalMessageListExtrasAreParsed(): void
    {
        $page = Page::fromArray([
            'data' => [],
            'limit' => 10,
            'skip' => 0,
            'total' => 0,
            'answeredBy' => ['engineId' => 'eng_1'],
            'nextCursor' => null,
            'hasMore' => false,
            'coverage' => ['truncated' => true],
            'autoLoadedPages' => 2,
            'sinceReached' => false,
            'sources' => ['backup' => 1],
        ], static fn (array $row): object => (object) $row);
        self::assertNotNull($page->answeredBy);
        self::assertSame('eng_1', $page->answeredBy->engineId);
        self::assertNull($page->nextCursor);
        self::assertFalse($page->hasMore);
        self::assertNotNull($page->coverage);
        self::assertTrue($page->coverage->truncated);
        self::assertSame(2, $page->autoLoadedPages);
        self::assertFalse($page->sinceReached);
        self::assertSame(['backup' => 1], $page->sources);
    }
}
