<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\BulkItemsResponse;
use PHPUnit\Framework\TestCase;

final class BulkItemsResponseTypeTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function fixture(): array
    {
        return [
            'created' => [
                [
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
                ],
            ],
            'updated' => [
                [
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
                ],
            ],
            'unchanged' => 3,
            'board' => [
                'board_id' => 'board_id_value',
                'name' => 'name_value',
                'description' => 'description_value',
                'domain' => 'domain_value',
                'web_url' => 'web_url_value',
            ],
        ];
    }


    public function testHappyPath(): void
    {
        $x = BulkItemsResponse::fromArray(self::fixture());
        self::assertSame(self::fixture()['unchanged'], $x->unchanged);
    }

    public function testMissingRequiredThrows(): void
    {
        $data = self::fixture();
        unset($data['unchanged']);
        $this->expectException(ValidationError::class);
        BulkItemsResponse::fromArray($data);
    }

    public function testWrongScalarThrows(): void
    {
        $data = self::fixture();
        $data['unchanged'] = 'nope';
        $this->expectException(ValidationError::class);
        BulkItemsResponse::fromArray($data);
    }
}
