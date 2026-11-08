<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\MessageWaLabelsResult;
use PHPUnit\Framework\TestCase;

final class MessageWaLabelsResultTypeTest extends TestCase
{
    public function testHappyPath(): void
    {
        $r = MessageWaLabelsResult::fromArray([
            'requested' => 2,
            'applied' => false,
            'error' => 'wa_business_required',
        ]);
        self::assertSame(2, $r->requested);
        self::assertFalse($r->applied);
        self::assertSame('wa_business_required', $r->error);
    }

    public function testAbsentErrorIsNull(): void
    {
        $r = MessageWaLabelsResult::fromArray(['requested' => 1, 'applied' => true]);
        self::assertNull($r->error);
    }

    public function testMissingAppliedThrows(): void
    {
        $this->expectException(ValidationError::class);
        MessageWaLabelsResult::fromArray(['requested' => 1]);
    }

    public function testWrongAppliedTypeThrows(): void
    {
        $this->expectException(ValidationError::class);
        MessageWaLabelsResult::fromArray(['requested' => 1, 'applied' => 'yes']);
    }
}
