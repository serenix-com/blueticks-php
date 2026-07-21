<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\BatchMessageAck;
use PHPUnit\Framework\TestCase;

final class BatchMessageAckTypeTest extends TestCase
{
    public function testHappyPath(): void
    {
        $a = BatchMessageAck::fromArray(['key' => 'k1', 'ack' => 2, 'found' => true]);
        self::assertSame('k1', $a->key);
        self::assertSame(2, $a->ack);
        self::assertTrue($a->found);
    }

    public function testNotFoundNullAck(): void
    {
        $a = BatchMessageAck::fromArray(['key' => 'k1', 'ack' => null, 'found' => false]);
        self::assertNull($a->ack);
        self::assertFalse($a->found);
    }

    public function testMissingRequiredThrows(): void
    {
        $this->expectException(ValidationError::class);
        BatchMessageAck::fromArray(['key' => 'k1']);
    }

    public function testWrongScalarThrows(): void
    {
        $this->expectException(ValidationError::class);
        BatchMessageAck::fromArray(['key' => 'k1', 'found' => 'yes']);
    }
}
