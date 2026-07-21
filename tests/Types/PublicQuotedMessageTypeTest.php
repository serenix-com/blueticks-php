<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\PublicQuotedMessage;
use PHPUnit\Framework\TestCase;

final class PublicQuotedMessageTypeTest extends TestCase
{
    public function testHappyPath(): void
    {
        $q = PublicQuotedMessage::fromArray([
            'waMessageKey' => ['fromMe' => true],
            'text' => 'earlier',
            'type' => 'chat',
            'author' => '1@c.us',
        ]);
        self::assertSame('earlier', $q->text);
        self::assertTrue($q->waMessageKey->fromMe);
    }

    public function testMissingRequiredThrows(): void
    {
        $this->expectException(ValidationError::class);
        PublicQuotedMessage::fromArray(['text' => 'earlier']);
    }

    public function testWrongScalarThrows(): void
    {
        $this->expectException(ValidationError::class);
        PublicQuotedMessage::fromArray(['waMessageKey' => ['fromMe' => true], 'text' => 99]);
    }
}
