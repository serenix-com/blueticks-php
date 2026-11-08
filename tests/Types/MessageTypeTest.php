<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\Message;
use PHPUnit\Framework\TestCase;

final class MessageTypeTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function fixture(): array
    {
        return [
            'id' => 'msg_1',
            'waMessageKey' => ['fromMe' => true, '_serialized' => 'true_1@c.us_ABC'],
            'to' => '1@c.us',
            'type' => 'poll',
            'status' => 'sent',
            'createdAt' => '2026-04-23T10:00:00Z',
            'pollQuestion' => 'Pick one',
            'pollOptions' => ['a', 'b'],
            'pollAllowMultiple' => false,
            'linkPreview' => ['title' => 'x'],
        ];
    }

    public function testHappyPath(): void
    {
        $m = Message::fromArray(self::fixture());
        self::assertSame('msg_1', $m->id);
        self::assertNotNull($m->waMessageKey);
        self::assertSame('true_1@c.us_ABC', $m->waMessageKey->_serialized);
        self::assertSame(['a', 'b'], $m->pollOptions);
        self::assertNotNull($m->linkPreview);
    }

    public function testMinimalRequiredOnly(): void
    {
        $m = Message::fromArray([
            'to' => '1@c.us',
            'type' => 'text',
            'status' => 'pending',
            'createdAt' => '2026-04-23T10:00:00Z',
        ]);
        self::assertNull($m->id);
        self::assertNull($m->waMessageKey);
        self::assertNull($m->pollOptions);
    }

    public function testMissingRequiredThrows(): void
    {
        $f = self::fixture();
        unset($f['status']);
        $this->expectException(ValidationError::class);
        Message::fromArray($f);
    }

    public function testInvalidTypeEnumThrows(): void
    {
        $f = self::fixture();
        $f['type'] = 'carrier-pigeon';
        $this->expectException(ValidationError::class);
        Message::fromArray($f);
    }

    public function testNewSpecFieldsAreParsed(): void
    {
        $m = Message::fromArray([
            'id' => 'msg_1',
            'to' => '1@c.us',
            'type' => 'text',
            'text' => 'hi',
            'status' => 'pending',
            'createdAt' => '2026-04-23T10:00:00Z',
            'recipientName' => 'Alice',
            'recurrence' => [
                'frequency' => 'monthly',
                'interval' => 1,
                'byWeekday' => null,
                'endsAfter' => null,
                'endsOn' => '2027-01-01T00:00:00Z',
                'timezone' => 'UTC',
            ],
            'waLabels' => ['requested' => 1, 'applied' => true, 'error' => null],
            'warning' => ['code' => 'no_engine_connected', 'message' => 'No engine.'],
        ]);
        self::assertSame('Alice', $m->recipientName);
        self::assertNotNull($m->recurrence);
        self::assertSame('monthly', $m->recurrence->frequency);
        self::assertNotNull($m->waLabels);
        self::assertTrue($m->waLabels->applied);
        self::assertNotNull($m->warning);
        self::assertSame('no_engine_connected', $m->warning->code);
    }

    public function testNewSpecFieldsDefaultToNull(): void
    {
        $m = Message::fromArray([
            'id' => 'msg_1',
            'to' => '1@c.us',
            'type' => 'text',
            'status' => 'pending',
            'createdAt' => '2026-04-23T10:00:00Z',
        ]);
        self::assertNull($m->recipientName);
        self::assertNull($m->recurrence);
        self::assertNull($m->waLabels);
        self::assertNull($m->warning);
    }
}
