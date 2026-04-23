<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\AppendContactsResult;
use PHPUnit\Framework\TestCase;

final class AppendContactsResultTest extends TestCase
{
    public function testFromArrayHappyPath(): void
    {
        $r = AppendContactsResult::fromArray([
            'added' => 5,
            'contact_count' => 42,
        ]);
        self::assertSame(5, $r->added);
        self::assertSame(42, $r->contactCount);
    }

    public function testMissingFieldThrows(): void
    {
        $this->expectException(ValidationError::class);
        AppendContactsResult::fromArray(['added' => 5]);
    }
}
