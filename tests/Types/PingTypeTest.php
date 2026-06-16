<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\Ping;
use PHPUnit\Framework\TestCase;

final class PingTypeTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function fixture(): array
    {
        return [
            'accountId' => 'acct_abc123',
            'keyPrefix' => 'bt_live_xy',
            'scopes' => ['messages:read', 'messages:write'],
        ];
    }

    public function testFromArrayHappyPath(): void
    {
        $ping = Ping::fromArray(self::fixture());
        self::assertSame('acct_abc123', $ping->accountId);
        self::assertSame('bt_live_xy', $ping->keyPrefix);
        self::assertSame(['messages:read', 'messages:write'], $ping->scopes);
    }

    public function testFromArrayMissingRequiredFieldThrows(): void
    {
        $this->expectException(ValidationError::class);
        $f = self::fixture();
        unset($f['accountId']);
        Ping::fromArray($f);
    }

    public function testFromArrayWrongTypeThrows(): void
    {
        $this->expectException(ValidationError::class);
        $f = self::fixture();
        $f['accountId'] = 123;
        Ping::fromArray($f);
    }

    public function testFromArrayNonArrayScopesThrows(): void
    {
        $this->expectException(ValidationError::class);
        $f = self::fixture();
        $f['scopes'] = 'not-an-array';
        Ping::fromArray($f);
    }

    public function testFromArrayScopesItemWrongTypeThrows(): void
    {
        $this->expectException(ValidationError::class);
        $f = self::fixture();
        $f['scopes'] = ['ok_item', 42];
        Ping::fromArray($f);
    }

    public function testFromArrayIgnoresExtraFields(): void
    {
        $f = self::fixture();
        $f['extra'] = 'ignored';
        $ping = Ping::fromArray($f);
        self::assertSame('acct_abc123', $ping->accountId);
    }
}
