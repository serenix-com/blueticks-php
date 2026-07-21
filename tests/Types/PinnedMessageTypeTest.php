<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\PinnedMessage;
use PHPUnit\Framework\TestCase;

final class PinnedMessageTypeTest extends TestCase
{
    public function testHappyPath(): void
    {
        $p = PinnedMessage::fromArray(['key' => 'k1', 'chatId' => '1@c.us', 'text' => 'pinned']);
        self::assertSame('k1', $p->key);
        self::assertSame('pinned', $p->text);
    }

    public function testMissingRequiredThrows(): void
    {
        $this->expectException(ValidationError::class);
        PinnedMessage::fromArray(['key' => 'k1']);
    }

    public function testWrongScalarThrows(): void
    {
        $this->expectException(ValidationError::class);
        PinnedMessage::fromArray(['key' => 'k1', 'chatId' => 1]);
    }
}
