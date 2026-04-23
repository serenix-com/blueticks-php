<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Types\Audience;
use PHPUnit\Framework\TestCase;

final class AudienceTest extends TestCase
{
    public function testFromArrayBase(): void
    {
        $a = Audience::fromArray([
            'id' => 'aud_1',
            'name' => 'Customers',
            'contact_count' => 42,
            'created_at' => '2026-04-23T10:00:00Z',
        ]);
        self::assertSame('aud_1', $a->id);
        self::assertSame('Customers', $a->name);
        self::assertSame(42, $a->contactCount);
        self::assertSame('2026-04-23T10:00:00Z', $a->createdAt);
        self::assertNull($a->contacts);
        self::assertNull($a->page);
        self::assertNull($a->hasMore);
    }

    public function testFromArrayWithContacts(): void
    {
        $a = Audience::fromArray([
            'id' => 'aud_1',
            'name' => 'Customers',
            'contact_count' => 1,
            'created_at' => '2026-04-23T10:00:00Z',
            'contacts' => [
                [
                    'id' => 'ct_1',
                    'to' => '+15551234567',
                    'variables' => ['name' => 'Alice'],
                    'added_at' => '2026-04-23T10:00:00Z',
                ],
            ],
            'page' => 1,
            'has_more' => false,
        ]);
        self::assertNotNull($a->contacts);
        self::assertCount(1, $a->contacts);
        self::assertSame('ct_1', $a->contacts[0]->id);
        self::assertSame(['name' => 'Alice'], $a->contacts[0]->variables);
        self::assertSame(1, $a->page);
        self::assertFalse($a->hasMore);
    }
}
