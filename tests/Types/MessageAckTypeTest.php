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
        self::assertSame(3, MessageAck::fromArray(['ack' => 3])->ack);
    }

    public function testNullAck(): void
    {
        self::assertNull(MessageAck::fromArray(['ack' => null])->ack);
    }

    public function testAbsentAck(): void
    {
        self::assertNull(MessageAck::fromArray([])->ack);
    }

    public function testWrongScalarThrows(): void
    {
        $this->expectException(ValidationError::class);
        MessageAck::fromArray(['ack' => '3']);
    }
}
