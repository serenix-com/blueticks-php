<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\SunoAccount;
use PHPUnit\Framework\TestCase;

final class SunoAccountTypeTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function fixture(): array
    {
        return [
            'creditsLeft' => 1200,
            'monthlyLimit' => 2500,
            'monthlyUsage' => 1300,
            'plan' => 'Pro Plan',
        ];
    }

    public function testHappyPath(): void
    {
        $a = SunoAccount::fromArray(self::fixture());
        self::assertSame(1200.0, $a->creditsLeft);
        self::assertSame(2500.0, $a->monthlyLimit);
        self::assertSame(1300.0, $a->monthlyUsage);
        self::assertSame('Pro Plan', $a->plan);
    }

    public function testNullableFieldsAccepted(): void
    {
        $f = self::fixture();
        $f['monthlyLimit'] = null;
        $f['monthlyUsage'] = null;
        $f['plan'] = null;
        $a = SunoAccount::fromArray($f);
        self::assertNull($a->monthlyLimit);
        self::assertNull($a->monthlyUsage);
        self::assertNull($a->plan);
    }

    public function testMissingRequiredThrows(): void
    {
        $f = self::fixture();
        unset($f['creditsLeft']);
        $this->expectException(ValidationError::class);
        SunoAccount::fromArray($f);
    }

    public function testWrongTypeCreditsThrows(): void
    {
        $f = self::fixture();
        $f['creditsLeft'] = 'lots';
        $this->expectException(ValidationError::class);
        SunoAccount::fromArray($f);
    }
}
