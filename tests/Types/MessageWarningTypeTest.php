<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\MessageWarning;
use PHPUnit\Framework\TestCase;

final class MessageWarningTypeTest extends TestCase
{
    public function testHappyPath(): void
    {
        $w = MessageWarning::fromArray(['code' => 'no_engine_connected', 'message' => 'No engine connected.']);
        self::assertSame('no_engine_connected', $w->code);
        self::assertSame('No engine connected.', $w->message);
    }

    public function testMissingCodeThrows(): void
    {
        $this->expectException(ValidationError::class);
        MessageWarning::fromArray(['message' => 'x']);
    }

    public function testUnknownCodeThrows(): void
    {
        $this->expectException(ValidationError::class);
        MessageWarning::fromArray(['code' => 'other', 'message' => 'x']);
    }

    public function testWrongMessageTypeThrows(): void
    {
        $this->expectException(ValidationError::class);
        MessageWarning::fromArray(['code' => 'no_engine_connected', 'message' => 5]);
    }
}
