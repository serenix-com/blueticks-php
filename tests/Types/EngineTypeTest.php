<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\Engine;
use PHPUnit\Framework\TestCase;

final class EngineTypeTest extends TestCase
{
    public function testHappyPath(): void
    {
        $e = Engine::fromArray([
            'id' => 'eng_1',
            'type' => 'gateway',
            'connected' => true,
            'state' => 'CONNECTED',
            'hasSynced' => true,
        ]);
        self::assertSame('eng_1', $e->id);
        self::assertSame('CONNECTED', $e->state);
        self::assertTrue($e->hasSynced);
    }

    public function testOptionalFieldsAbsentHydrateToNull(): void
    {
        // A CONNECTED engine omits state/stream/hasSynced entirely.
        $e = Engine::fromArray(['id' => 'eng_1', 'type' => 'gateway', 'connected' => true]);
        self::assertNull($e->state);
        self::assertNull($e->stream);
        self::assertNull($e->hasSynced);
    }

    public function testOptionalFieldsExplicitNullHydrateToNull(): void
    {
        // A DISCONNECTED engine returns state/stream/hasSynced as explicit null.
        $e = Engine::fromArray([
            'id' => 'eng_1',
            'type' => 'gateway',
            'connected' => false,
            'state' => null,
            'stream' => null,
            'hasSynced' => null,
        ]);
        self::assertNull($e->state);
        self::assertNull($e->stream);
        self::assertNull($e->hasSynced);
    }

    public function testMissingRequiredThrows(): void
    {
        $this->expectException(ValidationError::class);
        Engine::fromArray(['id' => 'eng_1', 'type' => 'gateway']);
    }

    public function testInvalidTypeEnumThrows(): void
    {
        $this->expectException(ValidationError::class);
        Engine::fromArray(['id' => 'eng_1', 'type' => 'quantum', 'connected' => true]);
    }
}
