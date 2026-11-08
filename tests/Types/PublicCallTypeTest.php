<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\PublicCall;
use PHPUnit\Framework\TestCase;

final class PublicCallTypeTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function fixture(): array
    {
        return [
            'direction' => 'in',
            'media' => 'voice',
            'outcome' => 'answered',
            'durationSec' => 42,
            'isGroup' => false,
        ];
    }

    public function testHappyPath(): void
    {
        $c = PublicCall::fromArray(self::fixture());
        self::assertSame('in', $c->direction);
        self::assertSame('voice', $c->media);
        self::assertSame('answered', $c->outcome);
        self::assertSame(42, $c->durationSec);
        self::assertFalse($c->isGroup);
    }

    public function testMissingDirectionThrows(): void
    {
        $f = self::fixture();
        unset($f['direction']);
        $this->expectException(ValidationError::class);
        PublicCall::fromArray($f);
    }

    public function testUnknownOutcomeThrows(): void
    {
        $f = self::fixture();
        $f['outcome'] = 'busy';
        $this->expectException(ValidationError::class);
        PublicCall::fromArray($f);
    }

    public function testWrongIsGroupThrows(): void
    {
        $f = self::fixture();
        $f['isGroup'] = 'no';
        $this->expectException(ValidationError::class);
        PublicCall::fromArray($f);
    }
}
