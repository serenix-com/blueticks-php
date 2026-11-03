<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\RealtimeTokenResponse;
use PHPUnit\Framework\TestCase;

final class RealtimeTokenResponseTypeTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function fixture(): array
    {
        return [
            'brokerUrl' => 'brokerUrl_value',
            'username' => 'username_value',
            'password' => 'password_value',
            'expiresAt' => '2026-11-01T10:00:00Z',
            'expiresInSeconds' => 3,
            'topics' => [
                'messages' => 'messages_value',
            ],
        ];
    }


    public function testHappyPath(): void
    {
        $x = RealtimeTokenResponse::fromArray(self::fixture());
        self::assertSame(self::fixture()['brokerUrl'], $x->brokerUrl);
        self::assertSame(self::fixture()['username'], $x->username);
        self::assertSame(self::fixture()['password'], $x->password);
        self::assertSame(self::fixture()['expiresAt'], $x->expiresAt);
    }

    public function testMissingRequiredThrows(): void
    {
        $data = self::fixture();
        unset($data['brokerUrl']);
        $this->expectException(ValidationError::class);
        RealtimeTokenResponse::fromArray($data);
    }

    public function testWrongScalarThrows(): void
    {
        $data = self::fixture();
        $data['brokerUrl'] = 12345;
        $this->expectException(ValidationError::class);
        RealtimeTokenResponse::fromArray($data);
    }
}
