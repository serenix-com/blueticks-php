<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\PublicMessage;
use PHPUnit\Framework\TestCase;

final class PublicMessageTypeTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function fixture(): array
    {
        return [
            'waMessageKey' => ['fromMe' => false, '_serialized' => 'false_1@c.us_XYZ'],
            'chatId' => '1@c.us',
            'from' => '1@c.us',
            'type' => 'chat',
            'fromMe' => false,
            'text' => 'hello',
            'ack' => 3,
            'quotedMessage' => [
                'waMessageKey' => ['fromMe' => true],
                'text' => 'earlier',
            ],
        ];
    }

    public function testHappyPath(): void
    {
        $m = PublicMessage::fromArray(self::fixture());
        self::assertSame('1@c.us', $m->chatId);
        self::assertSame(3, $m->ack);
        self::assertNotNull($m->quotedMessage);
        self::assertSame('earlier', $m->quotedMessage->text);
    }

    public function testMissingRequiredThrows(): void
    {
        $f = self::fixture();
        unset($f['from']);
        $this->expectException(ValidationError::class);
        PublicMessage::fromArray($f);
    }

    public function testWrongScalarThrows(): void
    {
        $f = self::fixture();
        $f['fromMe'] = 'no';
        $this->expectException(ValidationError::class);
        PublicMessage::fromArray($f);
    }

    public function testSourceAndSecret(): void
    {
        $f = self::fixture();
        $f['source'] = 'backup';
        $f['secret'] = 'corr-1';
        $m = PublicMessage::fromArray($f);
        self::assertSame('backup', $m->source);
        self::assertSame('corr-1', $m->secret);
        self::assertNull(PublicMessage::fromArray(self::fixture())->source);
    }

    public function testInvalidSourceThrows(): void
    {
        $f = self::fixture();
        $f['source'] = 'cloud';
        $this->expectException(ValidationError::class);
        PublicMessage::fromArray($f);
    }
}
