<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\BoardRef;
use PHPUnit\Framework\TestCase;

final class BoardRefTypeTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function fixture(): array
    {
        return [
            'board_id' => 'board_id_value',
            'name' => 'name_value',
            'description' => 'description_value',
            'domain' => 'domain_value',
            'web_url' => 'web_url_value',
        ];
    }


    public function testHappyPath(): void
    {
        $x = BoardRef::fromArray(self::fixture());
        self::assertSame(self::fixture()['board_id'], $x->board_id);
        self::assertSame(self::fixture()['name'], $x->name);
        self::assertSame(self::fixture()['description'], $x->description);
        self::assertSame(self::fixture()['domain'], $x->domain);
    }

    public function testMissingRequiredThrows(): void
    {
        $data = self::fixture();
        unset($data['board_id']);
        $this->expectException(ValidationError::class);
        BoardRef::fromArray($data);
    }

    public function testWrongScalarThrows(): void
    {
        $data = self::fixture();
        $data['board_id'] = 12345;
        $this->expectException(ValidationError::class);
        BoardRef::fromArray($data);
    }
}
