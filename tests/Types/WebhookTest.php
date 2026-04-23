<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\Webhook;
use Blueticks\Types\WebhookCreateResult;
use PHPUnit\Framework\TestCase;

final class WebhookTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function fixture(): array
    {
        return [
            'id' => 'wh_1',
            'url' => 'https://example.com/webhooks',
            'events' => ['message.delivered', 'message.failed'],
            'description' => 'primary',
            'status' => 'enabled',
            'created_at' => '2026-04-23T10:00:00Z',
        ];
    }

    public function testFromArrayHappyPath(): void
    {
        $w = Webhook::fromArray(self::fixture());
        self::assertSame('wh_1', $w->id);
        self::assertSame('https://example.com/webhooks', $w->url);
        self::assertSame(['message.delivered', 'message.failed'], $w->events);
        self::assertSame('primary', $w->description);
        self::assertSame('enabled', $w->status);
        self::assertSame('2026-04-23T10:00:00Z', $w->createdAt);
    }

    public function testNullableDescription(): void
    {
        $f = self::fixture();
        $f['description'] = null;
        $w = Webhook::fromArray($f);
        self::assertNull($w->description);
    }

    public function testWebhookCreateResultIncludesSecret(): void
    {
        $f = self::fixture();
        $f['secret'] = 'whsec_abc';
        $r = WebhookCreateResult::fromArray($f);
        self::assertSame('whsec_abc', $r->secret);
        self::assertSame('wh_1', $r->id);
    }

    public function testMissingSecretThrows(): void
    {
        $this->expectException(ValidationError::class);
        WebhookCreateResult::fromArray(self::fixture());
    }
}
