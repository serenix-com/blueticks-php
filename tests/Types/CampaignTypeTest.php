<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\Campaign;
use PHPUnit\Framework\TestCase;

final class CampaignTypeTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function fixture(): array
    {
        return [
            'cmpId' => 'cmp_1',
            'name' => 'Spring',
            'audienceId' => 'aud_1',
            'status' => 'running',
            'totalCount' => 10,
            'sentCount' => 4,
            'deliveredCount' => 3,
            'readCount' => 1,
            'failedCount' => 0,
            'createdAt' => '2026-04-23T10:00:00Z',
        ];
    }

    public function testHappyPath(): void
    {
        $c = Campaign::fromArray(self::fixture());
        self::assertSame('cmp_1', $c->cmpId);
        self::assertSame('running', $c->status);
        self::assertNull($c->completedAt);
    }

    public function testMissingRequiredThrows(): void
    {
        $f = self::fixture();
        unset($f['status']);
        $this->expectException(ValidationError::class);
        Campaign::fromArray($f);
    }

    public function testInvalidStatusEnumThrows(): void
    {
        $f = self::fixture();
        $f['status'] = 'exploded';
        $this->expectException(ValidationError::class);
        Campaign::fromArray($f);
    }

    public function testWrongScalarThrows(): void
    {
        $f = self::fixture();
        $f['sentCount'] = '4';
        $this->expectException(ValidationError::class);
        Campaign::fromArray($f);
    }
}
