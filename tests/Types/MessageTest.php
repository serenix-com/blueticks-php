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
            'id' => 'msg_1',
            'to' => '+15551234567',
            'from' => null,
            'text' => 'hello',
            'media_url' => null,
            'status' => 'queued',
            'send_at' => null,
            'created_at' => '2026-04-23T10:00:00Z',
            'sent_at' => null,
            'delivered_at' => null,
            'read_at' => null,
            'failed_at' => null,
            'failure_reason' => null,
        ];
    }

    public function testFromArrayHappyPath(): void
    {
        $m = Message::fromArray(self::fixture());
        self::assertSame('msg_1', $m->id);
        self::assertSame('+15551234567', $m->to);
        self::assertNull($m->from);
        self::assertSame('hello', $m->text);
        self::assertNull($m->media_url);
        self::assertSame('queued', $m->status);
        self::assertSame('2026-04-23T10:00:00Z', $m->created_at);
        self::assertNull($m->sent_at);
        self::assertNull($m->delivered_at);
        self::assertNull($m->read_at);
        self::assertNull($m->failed_at);
        self::assertNull($m->failure_reason);
    }

    public function testPopulatesAllOptionalFields(): void
    {
        $f = self::fixture();
        $f['media_url'] = 'https://cdn.example.com/x.jpg';
        $f['send_at'] = '2026-04-24T09:00:00Z';
        $f['sent_at'] = '2026-04-23T10:00:01Z';
        $f['delivered_at'] = '2026-04-23T10:00:02Z';
        $f['read_at'] = '2026-04-23T10:00:03Z';
        $f['failed_at'] = '2026-04-23T10:00:04Z';
        $f['failure_reason'] = 'blocked';
        $m = Message::fromArray($f);
        self::assertSame('https://cdn.example.com/x.jpg', $m->media_url);
        self::assertSame('2026-04-24T09:00:00Z', $m->send_at);
        self::assertSame('2026-04-23T10:00:01Z', $m->sent_at);
        self::assertSame('2026-04-23T10:00:02Z', $m->delivered_at);
        self::assertSame('2026-04-23T10:00:03Z', $m->read_at);
        self::assertSame('2026-04-23T10:00:04Z', $m->failed_at);
        self::assertSame('blocked', $m->failure_reason);
    }

    public function testFromArrayRequiresId(): void
    {
        $this->expectException(ValidationError::class);
        $f = self::fixture();
        unset($f['id']);
        Message::fromArray($f);
    }
}
