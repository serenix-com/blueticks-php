<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\WebhookEvent;
use PHPUnit\Framework\TestCase;

final class WebhookEventTest extends TestCase
{
    public function testFromArrayHappyPath(): void
    {
        $e = WebhookEvent::fromArray([
            'id' => 'evt_1',
            'type' => 'message.delivered',
            'created_at' => '2026-04-23T10:00:00Z',
            'data' => ['message_id' => 'msg_1'],
        ]);
        self::assertSame('evt_1', $e->id);
        self::assertSame('message.delivered', $e->type);
        self::assertSame('2026-04-23T10:00:00Z', $e->createdAt);
        self::assertSame(['message_id' => 'msg_1'], $e->data);
    }

    public function testMissingDataThrows(): void
    {
        $this->expectException(ValidationError::class);
        WebhookEvent::fromArray([
            'id' => 'evt_1',
            'type' => 'x',
            'created_at' => '2026-04-23T10:00:00Z',
        ]);
    }
}
