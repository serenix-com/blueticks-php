<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\PublicGroupEvent;
use PHPUnit\Framework\TestCase;

final class PublicGroupEventTypeTest extends TestCase
{
    public function testHappyPath(): void
    {
        $e = PublicGroupEvent::fromArray(['action' => 'add', 'actor' => '1@c.us', 'targets' => ['2@c.us']]);
        self::assertSame('add', $e->action);
        self::assertSame('1@c.us', $e->actor);
        self::assertSame(['2@c.us'], $e->targets);
    }

    public function testMissingActionThrows(): void
    {
        $this->expectException(ValidationError::class);
        PublicGroupEvent::fromArray(['actor' => '1@c.us']);
    }

    public function testUnknownActionThrows(): void
    {
        $this->expectException(ValidationError::class);
        PublicGroupEvent::fromArray(['action' => 'kick']);
    }

    public function testWrongTargetsThrows(): void
    {
        $this->expectException(ValidationError::class);
        PublicGroupEvent::fromArray(['action' => 'add', 'targets' => '2@c.us']);
    }
}
