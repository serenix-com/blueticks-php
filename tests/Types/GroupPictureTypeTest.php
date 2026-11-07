<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\GroupPicture;
use PHPUnit\Framework\TestCase;

final class GroupPictureTypeTest extends TestCase
{
    public function testHappyPath(): void
    {
        self::assertSame('https://pps.whatsapp.net/x.jpg', GroupPicture::fromArray(['url' => 'https://pps.whatsapp.net/x.jpg'])->url);
    }

    public function testNullUrlAllowed(): void
    {
        self::assertNull(GroupPicture::fromArray(['url' => null])->url);
    }

    public function testMissingUrlThrows(): void
    {
        $this->expectException(ValidationError::class);
        GroupPicture::fromArray([]);
    }

    public function testWrongTypeThrows(): void
    {
        $this->expectException(ValidationError::class);
        GroupPicture::fromArray(['url' => 5]);
    }
}
