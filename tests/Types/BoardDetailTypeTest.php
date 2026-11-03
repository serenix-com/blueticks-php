<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\BoardDetail;
use PHPUnit\Framework\TestCase;

final class BoardDetailTypeTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function fixture(): array
    {
        return [
            'board_id' => 'board_id_value',
            'name' => 'name_value',
            'description' => 'description_value',
            'domain' => 'domain_value',
            'agent_managed' => true,
            'memory_retention_days' => 3,
            'item_count' => 3,
            'web_url' => 'web_url_value',
            'created_at' => '2026-11-01T10:00:00Z',
            'updated_at' => '2026-11-01T10:00:00Z',
            'columns' => [
                [
                    'column_id' => 'column_id_value',
                    'title' => 'title_value',
                    'type' => 'type_value',
                    'options' => [
                        [
                            'label' => 'label_value',
                            'bgcolor' => 'bgcolor_value',
                        ],
                    ],
                ],
            ],
        ];
    }


    public function testHappyPath(): void
    {
        $x = BoardDetail::fromArray(self::fixture());
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
        BoardDetail::fromArray($data);
    }

    public function testWrongScalarThrows(): void
    {
        $data = self::fixture();
        $data['board_id'] = 12345;
        $this->expectException(ValidationError::class);
        BoardDetail::fromArray($data);
    }
}
