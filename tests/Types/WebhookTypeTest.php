<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\Webhook;
use PHPUnit\Framework\TestCase;

final class WebhookTypeTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function fixture(): array
    {
        return [
            'id' => 'wh_1',
            'url' => 'https://example.com/hook',
            'events' => ['message.delivered'],
            'description' => 'my hook',
            'status' => 'enabled',
            'createdAt' => '2026-04-23T10:00:00Z',
        ];
    }

    public function testHappyPath(): void
    {
        $wh = Webhook::fromArray(self::fixture());
        self::assertSame('wh_1', $wh->id);
        self::assertSame(['message.delivered'], $wh->events);
        self::assertSame('enabled', $wh->status);
    }

    public function testMissingRequiredThrows(): void
    {
        $f = self::fixture();
        unset($f['url']);
        $this->expectException(ValidationError::class);
        Webhook::fromArray($f);
    }

    public function testInvalidStatusEnumThrows(): void
    {
        $f = self::fixture();
        $f['status'] = 'paused';
        $this->expectException(ValidationError::class);
        Webhook::fromArray($f);
    }
}
