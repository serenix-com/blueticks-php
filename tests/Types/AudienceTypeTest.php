<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\Audience;
use PHPUnit\Framework\TestCase;

final class AudienceTypeTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function fixture(): array
    {
        return ['id' => 'aud_1', 'name' => 'Customers', 'contactCount' => 3, 'createdAt' => '2026-04-23T10:00:00Z'];
    }

    public function testHappyPath(): void
    {
        $a = Audience::fromArray(self::fixture());
        self::assertSame('aud_1', $a->id);
        self::assertSame(3, $a->contactCount);
    }

    public function testMissingRequiredThrows(): void
    {
        $f = self::fixture();
        unset($f['contactCount']);
        $this->expectException(ValidationError::class);
        Audience::fromArray($f);
    }

    public function testWrongScalarThrows(): void
    {
        $f = self::fixture();
        $f['contactCount'] = 'three';
        $this->expectException(ValidationError::class);
        Audience::fromArray($f);
    }
}
