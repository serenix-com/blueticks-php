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
            'media_url'      => null,
            'media_kind'     => null,
            'poll_question'  => null,
            'status'         => 'pending',
            'send_at'        => null,
            'created_at'     => '2026-04-23T10:00:00Z',
            'confirmed_at'   => null,
            'received_at'    => null,
            'read_at'        => null,
            'played_at'      => null,
            'failed_at'      => null,
            'failure_reason' => null,
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
        self::assertNull($m->media_url);
        self::assertNull($m->media_kind);
        self::assertNull($m->poll_question);
        self::assertSame('pending', $m->status);
        self::assertSame('2026-04-23T10:00:00Z', $m->created_at);
        self::assertNull($m->confirmed_at);
        self::assertNull($m->received_at);
        self::assertNull($m->read_at);
        self::assertNull($m->failed_at);
        self::assertNull($m->failure_reason);
    }

    public function testPopulatesMediaFields(): void
    {
        $f = self::fixture();
        $f['type'] = 'media';
        $f['media_url'] = 'https://cdn.example.com/x.jpg';
        $f['media_kind'] = 'image';
        $m = Message::fromArray($f);
        self::assertSame('media', $m->type);
        self::assertSame('https://cdn.example.com/x.jpg', $m->media_url);
        self::assertSame('image', $m->media_kind);
    }

    public function testPopulatesPollFields(): void
    {
        $f = self::fixture();
        $f['type'] = 'poll';
        $f['poll_question'] = 'Pizza?';
        $m = Message::fromArray($f);
        self::assertSame('poll', $m->type);
        self::assertSame('Pizza?', $m->poll_question);
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
