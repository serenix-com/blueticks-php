<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\MessageListCoverage;
use PHPUnit\Framework\TestCase;

final class MessageListCoverageTypeTest extends TestCase
{
    public function testHappyPath(): void
    {
        $c = MessageListCoverage::fromArray([
            'oldestLoadedTimestamp' => 1762596000000,
            'oldestLoadedIso' => '2025-11-08T10:00:00Z',
            'messagesScanned' => 120,
            'truncated' => true,
            'indexOnly' => false,
            'oldestScannedIso' => '2025-11-01T00:00:00Z',
        ]);
        self::assertSame(1762596000000.0, $c->oldestLoadedTimestamp);
        self::assertSame(120, $c->messagesScanned);
        self::assertTrue($c->truncated);
        self::assertFalse($c->indexOnly);
    }

    public function testAllFieldsOptional(): void
    {
        $c = MessageListCoverage::fromArray([]);
        self::assertNull($c->oldestLoadedTimestamp);
        self::assertNull($c->messagesScanned);
    }

    public function testWrongMessagesScannedThrows(): void
    {
        $this->expectException(ValidationError::class);
        MessageListCoverage::fromArray(['messagesScanned' => 'many']);
    }

    public function testWrongTruncatedThrows(): void
    {
        $this->expectException(ValidationError::class);
        MessageListCoverage::fromArray(['truncated' => 'no']);
    }
}
