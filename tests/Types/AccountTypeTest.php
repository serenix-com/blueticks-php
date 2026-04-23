<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\Account;
use PHPUnit\Framework\TestCase;

final class AccountTypeTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function fixture(): array
    {
        return [
            'id' => 'acct_abc123',
            'name' => 'Acme Corp',
            'timezone' => 'Europe/Berlin',
            'created_at' => '2026-04-23T10:00:00Z',
        ];
    }

    public function testFromArrayHappyPath(): void
    {
        $a = Account::fromArray(self::fixture());
        self::assertSame('acct_abc123', $a->id);
        self::assertSame('Acme Corp', $a->name);
        self::assertSame('Europe/Berlin', $a->timezone);
        self::assertSame('2026-04-23T10:00:00Z', $a->created_at);
    }

    public function testFromArrayAllowsNullTimezone(): void
    {
        $f = self::fixture();
        $f['timezone'] = null;
        $a = Account::fromArray($f);
        self::assertNull($a->timezone);
    }

    public function testFromArrayMissingRequiredFieldThrows(): void
    {
        $this->expectException(ValidationError::class);
        $f = self::fixture();
        unset($f['id']);
        Account::fromArray($f);
    }

    public function testFromArrayWrongTypeThrows(): void
    {
        $this->expectException(ValidationError::class);
        $f = self::fixture();
        $f['name'] = 123;
        Account::fromArray($f);
    }

    public function testFromArrayTimezoneWrongTypeThrows(): void
    {
        $this->expectException(ValidationError::class);
        $f = self::fixture();
        $f['timezone'] = 42;
        Account::fromArray($f);
    }

    public function testFromArrayIgnoresExtraFields(): void
    {
        $f = self::fixture();
        $f['extra_field'] = 'ignored';
        $a = Account::fromArray($f);
        self::assertSame('acct_abc123', $a->id);
    }
}
