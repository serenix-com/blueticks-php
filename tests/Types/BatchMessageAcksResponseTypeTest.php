<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\BatchMessageAckEntry;
use Blueticks\Types\BatchMessageAcksResponse;
use PHPUnit\Framework\TestCase;

final class BatchMessageAcksResponseTypeTest extends TestCase
{
    public function testHappyPath(): void
    {
        $r = BatchMessageAcksResponse::fromArray([
            'data' => [
                ['key' => 'true_1234@c.us_ABC', 'ack' => 3],
                ['key' => 'true_1234@c.us_DEF', 'ack' => null],
            ],
        ]);
        self::assertCount(2, $r->data);
        self::assertInstanceOf(BatchMessageAckEntry::class, $r->data[0]);
        self::assertSame('true_1234@c.us_ABC', $r->data[0]->key);
        self::assertSame(3, $r->data[0]->ack);
        self::assertSame('true_1234@c.us_DEF', $r->data[1]->key);
        self::assertNull($r->data[1]->ack);
    }

    public function testEmptyData(): void
    {
        $r = BatchMessageAcksResponse::fromArray(['data' => []]);
        self::assertSame([], $r->data);
    }

    public function testMissingDataThrows(): void
    {
        $this->expectException(ValidationError::class);
        BatchMessageAcksResponse::fromArray([]);
    }

    public function testNonArrayRowThrows(): void
    {
        $this->expectException(ValidationError::class);
        BatchMessageAcksResponse::fromArray(['data' => ['not-an-object']]);
    }

    public function testRowMissingKeyThrows(): void
    {
        $this->expectException(ValidationError::class);
        BatchMessageAcksResponse::fromArray(['data' => [['ack' => 1]]]);
    }
}
