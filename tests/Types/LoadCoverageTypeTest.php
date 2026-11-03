<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\LoadCoverage;
use PHPUnit\Framework\TestCase;

final class LoadCoverageTypeTest extends TestCase
{
    public function testHappyPath(): void
    {
        $x = LoadCoverage::fromArray([
            'oldestLoadedTimestamp' => 1700000000000,
            'oldestLoadedIso' => '2023-11-14T22:13:20Z',
        ]);
        self::assertSame(1700000000000.0, $x->oldestLoadedTimestamp);
        self::assertSame('2023-11-14T22:13:20Z', $x->oldestLoadedIso);
    }

    public function testEmptyIsAllNull(): void
    {
        $x = LoadCoverage::fromArray([]);
        self::assertNull($x->oldestLoadedTimestamp);
        self::assertNull($x->oldestLoadedIso);
    }

    public function testWrongScalarThrows(): void
    {
        $this->expectException(ValidationError::class);
        LoadCoverage::fromArray(['oldestLoadedIso' => 5]);
    }
}
