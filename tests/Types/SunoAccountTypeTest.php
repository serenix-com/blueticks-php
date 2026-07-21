<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\SunoAccount;
use PHPUnit\Framework\TestCase;

final class SunoAccountTypeTest extends TestCase
{
    public function testHappyPath(): void
    {
        $a = SunoAccount::fromArray([
            'creditsLeft' => 120.5,
            'monthlyLimit' => 500,
            'monthlyUsage' => 42.0,
            'plan' => 'Pro Plan',
        ]);
        self::assertSame(120.5, $a->creditsLeft);
        self::assertSame(500.0, $a->monthlyLimit);
        self::assertSame('Pro Plan', $a->plan);
    }

    public function testMissingRequiredThrows(): void
    {
        $this->expectException(ValidationError::class);
        SunoAccount::fromArray(['plan' => 'Pro Plan']);
    }

    public function testWrongScalarThrows(): void
    {
        $this->expectException(ValidationError::class);
        SunoAccount::fromArray(['creditsLeft' => 'lots']);
    }
}
