<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\Contact;
use PHPUnit\Framework\TestCase;

final class ContactTypeTest extends TestCase
{
    public function testHappyPath(): void
    {
        $c = Contact::fromArray(['chatId' => '1@c.us', 'name' => 'Alice', 'isBusiness' => true]);
        self::assertSame('1@c.us', $c->chatId);
        self::assertTrue($c->isBusiness);
    }

    public function testOptionalFieldsAbsent(): void
    {
        $c = Contact::fromArray(['chatId' => '1@c.us']);
        self::assertNull($c->name);
        self::assertNull($c->isBusiness);
    }

    public function testMissingRequiredThrows(): void
    {
        $this->expectException(ValidationError::class);
        Contact::fromArray(['name' => 'Alice']);
    }

    public function testWrongScalarThrows(): void
    {
        $this->expectException(ValidationError::class);
        Contact::fromArray(['chatId' => 1]);
    }
}
