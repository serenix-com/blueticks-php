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
            'id' => 'acc_1',
            'name' => 'Acme',
            'userEmail' => 'a@b.co',
            'timezone' => 'America/New_York',
            'createdAt' => '2026-04-23T10:00:00Z',
        ];
    }

    public function testHappyPath(): void
    {
        $a = Account::fromArray(self::fixture());
        self::assertSame('acc_1', $a->id);
        self::assertSame('a@b.co', $a->userEmail);
        self::assertSame('America/New_York', $a->timezone);
    }

    public function testOptionalFieldsMayBeAbsent(): void
    {
        $a = Account::fromArray(['id' => 'acc_1', 'name' => 'Acme', 'createdAt' => '2026-04-23T10:00:00Z']);
        self::assertNull($a->userEmail);
        self::assertNull($a->timezone);
    }

    public function testOptionalFieldsExplicitNull(): void
    {
        $a = Account::fromArray([
            'id' => 'acc_1',
            'name' => 'Acme',
            'userEmail' => null,
            'timezone' => null,
            'createdAt' => '2026-04-23T10:00:00Z',
        ]);
        self::assertNull($a->userEmail);
        self::assertNull($a->timezone);
    }

    public function testMissingRequiredThrows(): void
    {
        $f = self::fixture();
        unset($f['name']);
        $this->expectException(ValidationError::class);
        Account::fromArray($f);
    }

    public function testWrongScalarThrows(): void
    {
        $f = self::fixture();
        $f['id'] = 123;
        $this->expectException(ValidationError::class);
        Account::fromArray($f);
    }
}
