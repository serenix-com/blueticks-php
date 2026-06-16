<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\Message;
use PHPUnit\Framework\TestCase;

final class MessageTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function fixture(): array
    {
        return [
            'id'             => 'msg_1',
            'key'            => null,
            'to'             => '+15551234567',
            'from'           => null,
            'type'           => 'text',
            'text'           => 'hello',
            'mediaUrl'      => null,
            'mediaKind'     => null,
            'pollQuestion'  => null,
            'status'         => 'pending',
            'sendAt'        => null,
            'createdAt'     => '2026-04-23T10:00:00Z',
            'confirmedAt'   => null,
            'receivedAt'    => null,
            'readAt'        => null,
            'playedAt'      => null,
            'failedAt'      => null,
            'failureReason' => null,
        ];
    }

    public function testFromArrayHappyPath(): void
    {
        $m = Message::fromArray(self::fixture());
        self::assertSame('msg_1', $m->id);
        self::assertNull($m->key);
        self::assertSame('+15551234567', $m->to);
        self::assertNull($m->from);
        self::assertSame('text', $m->type);
        self::assertSame('hello', $m->text);
        self::assertNull($m->mediaUrl);
        self::assertNull($m->mediaKind);
        self::assertNull($m->pollQuestion);
        self::assertSame('pending', $m->status);
        self::assertSame('2026-04-23T10:00:00Z', $m->createdAt);
        self::assertNull($m->confirmedAt);
        self::assertNull($m->receivedAt);
        self::assertNull($m->readAt);
        self::assertNull($m->failedAt);
        self::assertNull($m->failureReason);
    }

    public function testPopulatesMediaFields(): void
    {
        $f = self::fixture();
        $f['type'] = 'media';
        $f['mediaUrl'] = 'https://cdn.example.com/x.jpg';
        $f['mediaKind'] = 'image';
        $m = Message::fromArray($f);
        self::assertSame('media', $m->type);
        self::assertSame('https://cdn.example.com/x.jpg', $m->mediaUrl);
        self::assertSame('image', $m->mediaKind);
    }

    public function testPopulatesPollFields(): void
    {
        $f = self::fixture();
        $f['type'] = 'poll';
        $f['pollQuestion'] = 'Pizza?';
        $m = Message::fromArray($f);
        self::assertSame('poll', $m->type);
        self::assertSame('Pizza?', $m->pollQuestion);
    }

    public function testFromArrayRequiresId(): void
    {
        $this->expectException(ValidationError::class);
        $f = self::fixture();
        unset($f['id']);
        Message::fromArray($f);
    }

    public function testFromArrayRequiresType(): void
    {
        $this->expectException(ValidationError::class);
        $f = self::fixture();
        $f['type'] = 42;
        Message::fromArray($f);
    }
}
