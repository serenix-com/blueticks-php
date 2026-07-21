<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\Ping;
use PHPUnit\Framework\TestCase;

final class PingTypeTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function fixture(): array
    {
        return [
            'api' => 'ok',
            'accountId' => 'acc_1',
            'whatsappConnections' => [
                ['id' => 'eng_1', 'type' => 'regular', 'connected' => true],
            ],
        ];
    }

    public function testHappyPath(): void
    {
        $p = Ping::fromArray(self::fixture());
        self::assertSame('acc_1', $p->accountId);
        self::assertCount(1, $p->whatsappConnections);
        self::assertSame('regular', $p->whatsappConnections[0]->type);
    }

    public function testMissingRequiredThrows(): void
    {
        $f = self::fixture();
        unset($f['accountId']);
        $this->expectException(ValidationError::class);
        Ping::fromArray($f);
    }

    public function testWrongApiValueThrows(): void
    {
        $f = self::fixture();
        $f['api'] = 'down';
        $this->expectException(ValidationError::class);
        Ping::fromArray($f);
    }
}
