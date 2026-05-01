<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\BatchMessageAckEntry;
use PHPUnit\Framework\TestCase;

final class BatchMessageAckEntryTypeTest extends TestCase
{
    public function testHappyPath(): void
    {
        $e = BatchMessageAckEntry::fromArray([
            'key' => 'true_1234@c.us_ABC',
            'ack' => 3,
        ]);
        self::assertSame('true_1234@c.us_ABC', $e->key);
        self::assertSame(3, $e->ack);
    }

    public function testNullAck(): void
    {
        $e = BatchMessageAckEntry::fromArray([
            'key' => 'true_1234@c.us_DEF',
            'ack' => null,
        ]);
        self::assertSame('true_1234@c.us_DEF', $e->key);
        self::assertNull($e->ack);
    }

    public function testMissingKeyThrows(): void
    {
        $this->expectException(ValidationError::class);
        BatchMessageAckEntry::fromArray(['ack' => 1]);
    }

    public function testMissingAckThrows(): void
    {
        $this->expectException(ValidationError::class);
        BatchMessageAckEntry::fromArray(['key' => 'k']);
    }

    public function testWrongAckTypeThrows(): void
    {
        $this->expectException(ValidationError::class);
        BatchMessageAckEntry::fromArray(['key' => 'k', 'ack' => 'three']);
    }

    public function testWrongKeyTypeThrows(): void
    {
        $this->expectException(ValidationError::class);
        BatchMessageAckEntry::fromArray(['key' => 123, 'ack' => 1]);
    }
}
