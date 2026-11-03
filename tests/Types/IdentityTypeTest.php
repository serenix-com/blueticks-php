<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\Identity;
use PHPUnit\Framework\TestCase;

final class IdentityTypeTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function fixture(): array
    {
        return [
            'input' => 'input_value',
            'resolved' => true,
            'canonical' => 'canonical_value',
            'phone' => 'phone_value',
            'lid' => 'lid_value',
            'aliases' => ['a', 'b'],
            'type' => 'user',
            'source' => 'engine',
        ];
    }


    public function testHappyPath(): void
    {
        $x = Identity::fromArray(self::fixture());
        self::assertSame(self::fixture()['input'], $x->input);
        self::assertSame(self::fixture()['resolved'], $x->resolved);
        self::assertSame(self::fixture()['canonical'], $x->canonical);
        self::assertSame(self::fixture()['phone'], $x->phone);
    }

    public function testMissingRequiredThrows(): void
    {
        $data = self::fixture();
        unset($data['input']);
        $this->expectException(ValidationError::class);
        Identity::fromArray($data);
    }

    public function testWrongScalarThrows(): void
    {
        $data = self::fixture();
        $data['input'] = 12345;
        $this->expectException(ValidationError::class);
        Identity::fromArray($data);
    }
}
