<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\ChatMessage;
use PHPUnit\Framework\TestCase;

final class ChatMessageTypeTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function fixture(): array
    {
        return [
            'key' => 'true_972544325389@c.us_3EB0ABCDEF',
            'chat_id' => '972544325389@c.us',
            'from' => '972544325389@c.us',
            'timestamp' => '2026-04-29T12:34:56Z',
            'text' => 'hello world',
            'type' => 'chat',
            'from_me' => false,
            'ack' => 3,
            'media_url' => null,
            'caption' => null,
            'filename' => null,
        ];
    }

    public function testHappyPath(): void
    {
        $m = ChatMessage::fromArray(self::fixture());
        self::assertSame('hello world', $m->text);
        self::assertSame('chat', $m->type);
        self::assertFalse($m->from_me);
        self::assertSame(3, $m->ack);
    }

    public function testDocumentMessageWithFilename(): void
    {
        $f = self::fixture();
        $f['type'] = 'document';
        $f['text'] = null;
        $f['filename'] = 'invoice.pdf';
        $f['media_url'] = 'https://cdn.example.com/abc.pdf';
        $m = ChatMessage::fromArray($f);
        self::assertSame('document', $m->type);
        self::assertNull($m->text);
        self::assertSame('invoice.pdf', $m->filename);
        self::assertSame('https://cdn.example.com/abc.pdf', $m->media_url);
    }

    public function testMissingRequiredKeyThrows(): void
    {
        $this->expectException(ValidationError::class);
        $f = self::fixture();
        unset($f['key']);
        ChatMessage::fromArray($f);
    }

    public function testWrongTypeAckThrows(): void
    {
        $this->expectException(ValidationError::class);
        $f = self::fixture();
        $f['ack'] = '3';
        ChatMessage::fromArray($f);
    }

    public function testWrongTypeFromMeThrows(): void
    {
        $this->expectException(ValidationError::class);
        $f = self::fixture();
        $f['from_me'] = 0;
        ChatMessage::fromArray($f);
    }
}
