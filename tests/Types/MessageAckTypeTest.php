<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\MessageAck;
use PHPUnit\Framework\TestCase;

final class MessageAckTypeTest extends TestCase
{
    public function testHappyPath(): void
    {
        $a = MessageAck::fromArray(['ack' => 3]);
        self::assertSame(3, $a->ack);
    }

    public function testNullAck(): void
    {
        $a = MessageAck::fromArray(['ack' => null]);
        self::assertNull($a->ack);
    }

    public function testMissingAckThrows(): void
    {
        $this->expectException(ValidationError::class);
        MessageAck::fromArray([]);
    }

    public function testWrongTypeThrows(): void
    {
        $this->expectException(ValidationError::class);
        MessageAck::fromArray(['ack' => '3']);
    }
}
