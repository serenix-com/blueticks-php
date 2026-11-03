<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\BoardColumn;
use PHPUnit\Framework\TestCase;

final class BoardColumnTypeTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function fixture(): array
    {
        return [
            'column_id' => 'column_id_value',
            'title' => 'title_value',
            'type' => 'type_value',
            'options' => [
                [
                    'label' => 'label_value',
                    'bgcolor' => 'bgcolor_value',
                ],
            ],
        ];
    }


    public function testHappyPath(): void
    {
        $x = BoardColumn::fromArray(self::fixture());
        self::assertSame(self::fixture()['column_id'], $x->column_id);
        self::assertSame(self::fixture()['title'], $x->title);
        self::assertSame(self::fixture()['type'], $x->type);
    }

    public function testMissingRequiredThrows(): void
    {
        $data = self::fixture();
        unset($data['column_id']);
        $this->expectException(ValidationError::class);
        BoardColumn::fromArray($data);
    }

    public function testWrongScalarThrows(): void
    {
        $data = self::fixture();
        $data['column_id'] = 12345;
        $this->expectException(ValidationError::class);
        BoardColumn::fromArray($data);
    }
}
