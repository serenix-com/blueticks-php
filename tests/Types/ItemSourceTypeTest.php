<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\ItemSource;
use PHPUnit\Framework\TestCase;

final class ItemSourceTypeTest extends TestCase
{
    public function testHappyPath(): void
    {
        $x = ItemSource::fromArray(['chat_id' => '1@c.us', 'excerpt' => 'hello', 'extracted_at' => 5]);
        self::assertSame('1@c.us', $x->chat_id);
        self::assertSame(5, $x->extracted_at);
        self::assertNull($x->chat_name);
    }

    public function testAllFieldsOptional(): void
    {
        $x = ItemSource::fromArray([]);
        self::assertNull($x->chat_id);
        self::assertNull($x->message_key);
    }

    public function testWrongScalarThrows(): void
    {
        $this->expectException(ValidationError::class);
        ItemSource::fromArray(['extracted_at' => 'yesterday']);
    }
}
