<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\GroupPictureRemoved;
use PHPUnit\Framework\TestCase;

final class GroupPictureRemovedTypeTest extends TestCase
{
    public function testOkTrue(): void
    {
        self::assertTrue(GroupPictureRemoved::fromArray(['ok' => true])->ok);
    }

    public function testOkFalseAllowed(): void
    {
        self::assertFalse(GroupPictureRemoved::fromArray(['ok' => false])->ok);
    }

    public function testMissingOkThrows(): void
    {
        $this->expectException(ValidationError::class);
        GroupPictureRemoved::fromArray([]);
    }
}
