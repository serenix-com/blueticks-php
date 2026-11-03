<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\OptionRef;
use PHPUnit\Framework\TestCase;

final class OptionRefTypeTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function fixture(): array
    {
        return [
            'label' => 'label_value',
            'bgcolor' => 'bgcolor_value',
        ];
    }


    public function testHappyPath(): void
    {
        $x = OptionRef::fromArray(self::fixture());
        self::assertSame(self::fixture()['label'], $x->label);
        self::assertSame(self::fixture()['bgcolor'], $x->bgcolor);
    }

    public function testMissingRequiredThrows(): void
    {
        $data = self::fixture();
        unset($data['label']);
        $this->expectException(ValidationError::class);
        OptionRef::fromArray($data);
    }

    public function testWrongScalarThrows(): void
    {
        $data = self::fixture();
        $data['label'] = 12345;
        $this->expectException(ValidationError::class);
        OptionRef::fromArray($data);
    }
}
