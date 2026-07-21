<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\WaMessageKey;
use PHPUnit\Framework\TestCase;

final class WaMessageKeyTypeTest extends TestCase
{
    public function testHappyPath(): void
    {
        $k = WaMessageKey::fromArray([
            'fromMe' => true,
            'remote' => '1@c.us',
            'id' => 'ABC',
            '_serialized' => 'true_1@c.us_ABC',
            'participant' => null,
        ]);
        self::assertTrue($k->fromMe);
        self::assertSame('true_1@c.us_ABC', $k->_serialized);
        self::assertNull($k->participant);
    }

    public function testMissingRequiredThrows(): void
    {
        $this->expectException(ValidationError::class);
        WaMessageKey::fromArray(['id' => 'ABC']);
    }

    public function testWrongScalarThrows(): void
    {
        $this->expectException(ValidationError::class);
        WaMessageKey::fromArray(['fromMe' => 'yes']);
    }
}
