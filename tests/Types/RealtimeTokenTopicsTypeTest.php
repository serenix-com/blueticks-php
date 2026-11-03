<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\RealtimeTokenTopics;
use PHPUnit\Framework\TestCase;

final class RealtimeTokenTopicsTypeTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function fixture(): array
    {
        return [
            'messages' => 'messages_value',
        ];
    }


    public function testHappyPath(): void
    {
        $x = RealtimeTokenTopics::fromArray(self::fixture());
        self::assertSame(self::fixture()['messages'], $x->messages);
    }

    public function testMissingRequiredThrows(): void
    {
        $data = self::fixture();
        unset($data['messages']);
        $this->expectException(ValidationError::class);
        RealtimeTokenTopics::fromArray($data);
    }

    public function testWrongScalarThrows(): void
    {
        $data = self::fixture();
        $data['messages'] = 12345;
        $this->expectException(ValidationError::class);
        RealtimeTokenTopics::fromArray($data);
    }
}
