<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\RecurrenceResponse;
use PHPUnit\Framework\TestCase;

final class RecurrenceResponseTypeTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function fixture(): array
    {
        return [
            'frequency' => 'weekly',
            'interval' => 2,
            'byWeekday' => ['MO', 'FR'],
            'endsAfter' => 10,
            'endsOn' => null,
            'timezone' => 'Asia/Jerusalem',
        ];
    }

    public function testHappyPath(): void
    {
        $r = RecurrenceResponse::fromArray(self::fixture());
        self::assertSame('weekly', $r->frequency);
        self::assertSame(2, $r->interval);
        self::assertSame(['MO', 'FR'], $r->byWeekday);
        self::assertSame(10, $r->endsAfter);
        self::assertNull($r->endsOn);
        self::assertSame('Asia/Jerusalem', $r->timezone);
    }

    public function testMissingFrequencyThrows(): void
    {
        $f = self::fixture();
        unset($f['frequency']);
        $this->expectException(ValidationError::class);
        RecurrenceResponse::fromArray($f);
    }

    public function testWrongIntervalThrows(): void
    {
        $f = self::fixture();
        $f['interval'] = 'two';
        $this->expectException(ValidationError::class);
        RecurrenceResponse::fromArray($f);
    }

    public function testUnknownFrequencyThrows(): void
    {
        $f = self::fixture();
        $f['frequency'] = 'hourly';
        $this->expectException(ValidationError::class);
        RecurrenceResponse::fromArray($f);
    }
}
