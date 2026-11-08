<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\MessageAnsweredBy;
use PHPUnit\Framework\TestCase;

final class MessageAnsweredByTypeTest extends TestCase
{
    public function testHappyPath(): void
    {
        $a = MessageAnsweredBy::fromArray([
            'engineId' => 'eng_1',
            'numberId' => 'num_1',
            'phoneNumber' => '972500000000',
        ]);
        self::assertSame('eng_1', $a->engineId);
        self::assertSame('num_1', $a->numberId);
        self::assertSame('972500000000', $a->phoneNumber);
    }

    public function testOnlyEngineIdRequired(): void
    {
        $a = MessageAnsweredBy::fromArray(['engineId' => 'eng_1']);
        self::assertNull($a->numberId);
        self::assertNull($a->phoneNumber);
    }

    public function testMissingEngineIdThrows(): void
    {
        $this->expectException(ValidationError::class);
        MessageAnsweredBy::fromArray(['numberId' => 'num_1']);
    }

    public function testWrongEngineIdThrows(): void
    {
        $this->expectException(ValidationError::class);
        MessageAnsweredBy::fromArray(['engineId' => 5]);
    }
}
