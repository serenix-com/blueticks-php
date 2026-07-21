<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\AppendContactsResult;
use PHPUnit\Framework\TestCase;

final class AppendContactsResultTypeTest extends TestCase
{
    public function testHappyPath(): void
    {
        $r = AppendContactsResult::fromArray(['added' => 2, 'contactCount' => 5]);
        self::assertSame(2, $r->added);
        self::assertSame(5, $r->contactCount);
    }

    public function testMissingRequiredThrows(): void
    {
        $this->expectException(ValidationError::class);
        AppendContactsResult::fromArray(['added' => 2]);
    }

    public function testWrongScalarThrows(): void
    {
        $this->expectException(ValidationError::class);
        AppendContactsResult::fromArray(['added' => 2, 'contactCount' => '5']);
    }
}
