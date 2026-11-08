<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\WaLabel;
use PHPUnit\Framework\TestCase;

final class WaLabelTypeTest extends TestCase
{
    public function testHappyPath(): void
    {
        $l = WaLabel::fromArray(['id' => 'lbl_1', 'name' => 'VIP', 'color' => '#ff0000']);
        self::assertSame('lbl_1', $l->id);
        self::assertSame('VIP', $l->name);
        self::assertSame('#ff0000', $l->color);
    }

    public function testAbsentNameAndColorAreNull(): void
    {
        $l = WaLabel::fromArray(['id' => 'lbl_2']);
        self::assertNull($l->name);
        self::assertNull($l->color);
    }

    public function testMissingIdThrows(): void
    {
        $this->expectException(ValidationError::class);
        WaLabel::fromArray(['name' => 'VIP']);
    }

    public function testWrongScalarThrows(): void
    {
        $this->expectException(ValidationError::class);
        WaLabel::fromArray(['id' => 7]);
    }
}
