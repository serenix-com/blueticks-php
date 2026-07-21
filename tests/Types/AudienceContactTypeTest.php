<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\AudienceContact;
use PHPUnit\Framework\TestCase;

final class AudienceContactTypeTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function fixture(): array
    {
        return [
            'id' => 'ct_1',
            'to' => '+15551234567',
            'variables' => ['firstName' => 'Alice'],
            'addedAt' => '2026-04-23T10:00:00Z',
        ];
    }

    public function testHappyPath(): void
    {
        $c = AudienceContact::fromArray(self::fixture());
        self::assertSame('ct_1', $c->id);
        self::assertSame('Alice', $c->variables['firstName']);
    }

    public function testMissingRequiredThrows(): void
    {
        $f = self::fixture();
        unset($f['to']);
        $this->expectException(ValidationError::class);
        AudienceContact::fromArray($f);
    }

    public function testWrongVariablesTypeThrows(): void
    {
        $f = self::fixture();
        $f['variables'] = 'nope';
        $this->expectException(ValidationError::class);
        AudienceContact::fromArray($f);
    }
}
