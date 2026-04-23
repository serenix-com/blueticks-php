<?php

declare(strict_types=1);

namespace Blueticks\Tests;

use Blueticks\Version;
use PHPUnit\Framework\TestCase;

final class VersionTest extends TestCase
{
    public function testVersionConstantMatchesExpected(): void
    {
        self::assertSame('1.0.0', Version::BLUETICKS_VERSION);
    }
}
