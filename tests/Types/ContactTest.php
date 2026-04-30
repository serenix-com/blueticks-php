<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\Contact;
use PHPUnit\Framework\TestCase;

final class ContactTest extends TestCase
{
    public function testFromArrayHappyPath(): void
    {
        $c = Contact::fromArray([
            'id' => 'ct_1',
            'to' => '+15551234567',
            'variables' => ['name' => 'Alice', 'plan' => 'pro'],
            'added_at' => '2026-04-23T10:00:00Z',
        ]);
        self::assertSame('ct_1', $c->id);
        self::assertSame('+15551234567', $c->to);
        self::assertSame(['name' => 'Alice', 'plan' => 'pro'], $c->variables);
        self::assertSame('2026-04-23T10:00:00Z', $c->added_at);
    }

    public function testNonStringVariableValueThrows(): void
    {
        $this->expectException(ValidationError::class);
        Contact::fromArray([
            'id' => 'ct_1',
            'to' => '+1',
            'variables' => ['name' => 42],
            'added_at' => '2026-04-23T10:00:00Z',
        ]);
    }
}
