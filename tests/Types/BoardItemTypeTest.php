<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\BoardItem;
use PHPUnit\Framework\TestCase;

final class BoardItemTypeTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function fixture(): array
    {
        return [
            'item_id' => 'item_id_value',
            'text' => 'text_value',
            'status' => [
                'label' => 'label_value',
                'bgcolor' => 'bgcolor_value',
            ],
            'priority' => [
                'label' => 'label_value',
                'bgcolor' => 'bgcolor_value',
            ],
            'due_date' => 'due_date_value',
            'cells' => [
                'k' => 'v',
            ],
            'source' => [
                'chat_id' => 'chat_id_value',
                'chat_name' => 'chat_name_value',
                'message_key' => 'message_key_value',
                'excerpt' => 'excerpt_value',
                'extracted_at' => 3,
            ],
            'confidence' => 0.5,
            'last_seen_at' => 3,
            'web_url' => 'web_url_value',
            'created_at' => '2026-11-01T10:00:00Z',
            'updated_at' => '2026-11-01T10:00:00Z',
        ];
    }


    public function testHappyPath(): void
    {
        $x = BoardItem::fromArray(self::fixture());
        self::assertSame(self::fixture()['item_id'], $x->item_id);
        self::assertSame(self::fixture()['text'], $x->text);
    }

    public function testMissingRequiredThrows(): void
    {
        $data = self::fixture();
        unset($data['item_id']);
        $this->expectException(ValidationError::class);
        BoardItem::fromArray($data);
    }

    public function testWrongScalarThrows(): void
    {
        $data = self::fixture();
        $data['item_id'] = 12345;
        $this->expectException(ValidationError::class);
        BoardItem::fromArray($data);
    }
}
