<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\NewsletterListItem;
use PHPUnit\Framework\TestCase;

final class NewsletterListItemTypeTest extends TestCase
{
    public function testHappyPath(): void
    {
        $n = NewsletterListItem::fromArray([
            'chatId' => '120@newsletter',
            'name' => 'News',
            'subscribers' => 10,
        ]);
        self::assertSame('120@newsletter', $n->chatId);
        self::assertSame(10, $n->subscribers);
    }

    public function testMissingRequiredThrows(): void
    {
        $this->expectException(ValidationError::class);
        NewsletterListItem::fromArray(['name' => 'News']);
    }

    public function testWrongScalarThrows(): void
    {
        $this->expectException(ValidationError::class);
        NewsletterListItem::fromArray(['chatId' => 120, 'name' => 'News']);
    }
}
