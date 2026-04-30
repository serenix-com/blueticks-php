<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Types\Campaign;
use PHPUnit\Framework\TestCase;

final class CampaignTest extends TestCase
{
    public function testFromArrayHappyPath(): void
    {
        $c = Campaign::fromArray([
            'id' => 'camp_1',
            'name' => 'Spring',
            'audience_id' => 'aud_1',
            'status' => 'running',
            'total_count' => 100,
            'sent_count' => 40,
            'delivered_count' => 35,
            'read_count' => 20,
            'failed_count' => 1,
            'from' => null,
            'created_at' => '2026-04-23T10:00:00Z',
            'started_at' => '2026-04-23T10:01:00Z',
            'completed_at' => null,
            'aborted_at' => null,
        ]);
        self::assertSame('camp_1', $c->id);
        self::assertSame('Spring', $c->name);
        self::assertSame('aud_1', $c->audience_id);
        self::assertSame('running', $c->status);
        self::assertSame(100, $c->total_count);
        self::assertSame(40, $c->sent_count);
        self::assertSame(35, $c->delivered_count);
        self::assertSame(20, $c->read_count);
        self::assertSame(1, $c->failed_count);
        self::assertNull($c->from);
        self::assertSame('2026-04-23T10:00:00Z', $c->created_at);
        self::assertSame('2026-04-23T10:01:00Z', $c->started_at);
        self::assertNull($c->completed_at);
        self::assertNull($c->aborted_at);
    }
}
