<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\PingConnection;
use PHPUnit\Framework\TestCase;

final class PingConnectionTypeTest extends TestCase
{
    public function testHappyPath(): void
    {
        $c = PingConnection::fromArray(['id' => 'eng_1', 'type' => 'gateway', 'connected' => true]);
        self::assertSame('eng_1', $c->id);
        self::assertSame('gateway', $c->type);
    }

    public function testMissingRequiredThrows(): void
    {
        $this->expectException(ValidationError::class);
        PingConnection::fromArray(['id' => 'eng_1', 'connected' => true]);
    }

    public function testInvalidTypeEnumThrows(): void
    {
        $this->expectException(ValidationError::class);
        PingConnection::fromArray(['id' => 'eng_1', 'type' => 'satellite', 'connected' => true]);
    }
}
