<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\Newsletter;
use Blueticks\Types\NewsletterListItem;
use PHPUnit\Framework\TestCase;

final class NewsletterTypeTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function fixture(): array
    {
        return [
            'newsletterId' => '120363201733549020@newsletter',
            'name'         => 'My Channel',
            'description'  => 'Weekly updates',
            'createdAt'    => '2024-01-15T10:00:00Z',
            'subscribers'  => 42,
            'invite'       => 'abc123def456',
            'verification' => 'UNVERIFIED',
        ];
    }

    /** @return array<string, mixed> */
    private static function listFixture(): array
    {
        return [
            'chatId'       => '120363201733549020@newsletter',
            'name'         => 'My Channel',
            'description'  => 'Weekly updates',
            'createdAt'    => '2024-01-15T10:00:00Z',
            'subscribers'  => 42,
            'invite'       => 'abc123def456',
            'verification' => 'UNVERIFIED',
        ];
    }

    public function testFromArrayHappyPath(): void
    {
        $nl = Newsletter::fromArray(self::fixture());
        self::assertSame('120363201733549020@newsletter', $nl->newsletterId);
        self::assertSame('My Channel', $nl->name);
        self::assertSame('Weekly updates', $nl->description);
        self::assertSame('2024-01-15T10:00:00Z', $nl->createdAt);
        self::assertSame(42, $nl->subscribers);
        self::assertSame('abc123def456', $nl->invite);
        self::assertSame('UNVERIFIED', $nl->verification);
    }

    public function testFromArrayNullableFieldsAreAllowedNull(): void
    {
        $f = self::fixture();
        $f['description']  = null;
        $f['createdAt']    = null;
        $f['subscribers']  = null;
        $f['invite']       = null;
        $f['verification'] = null;
        $nl = Newsletter::fromArray($f);
        self::assertNull($nl->description);
        self::assertNull($nl->createdAt);
        self::assertNull($nl->subscribers);
        self::assertNull($nl->invite);
        self::assertNull($nl->verification);
    }

    public function testFromArrayVerifiedEnumValue(): void
    {
        $f = self::fixture();
        $f['verification'] = 'VERIFIED';
        $nl = Newsletter::fromArray($f);
        self::assertSame('VERIFIED', $nl->verification);
    }

    public function testFromArrayMissingRequiredNewsletterIdThrows(): void
    {
        $this->expectException(ValidationError::class);
        $f = self::fixture();
        unset($f['newsletterId']);
        Newsletter::fromArray($f);
    }

    public function testFromArrayWrongTypeForNewsletterIdThrows(): void
    {
        $this->expectException(ValidationError::class);
        $f = self::fixture();
        $f['newsletterId'] = 123;
        Newsletter::fromArray($f);
    }

    public function testFromArrayInvalidVerificationValueThrows(): void
    {
        $this->expectException(ValidationError::class);
        $f = self::fixture();
        $f['verification'] = 'PENDING';
        Newsletter::fromArray($f);
    }

    public function testFromArrayMissingNullableKeyThrows(): void
    {
        $this->expectException(ValidationError::class);
        $f = self::fixture();
        unset($f['description']);
        Newsletter::fromArray($f);
    }

    public function testFromArrayIgnoresExtraFields(): void
    {
        $f = self::fixture();
        $f['extra_field'] = 'ignored';
        $nl = Newsletter::fromArray($f);
        self::assertSame('120363201733549020@newsletter', $nl->newsletterId);
    }

    // --- NewsletterListItem (list rows keyed by chatId) ---

    public function testListItemFromArrayHappyPath(): void
    {
        $item = NewsletterListItem::fromArray(self::listFixture());
        self::assertSame('120363201733549020@newsletter', $item->chatId);
        self::assertSame('My Channel', $item->name);
        self::assertSame('Weekly updates', $item->description);
        self::assertSame('2024-01-15T10:00:00Z', $item->createdAt);
        self::assertSame(42, $item->subscribers);
        self::assertSame('abc123def456', $item->invite);
        self::assertSame('UNVERIFIED', $item->verification);
    }

    public function testListItemFromArrayMissingRequiredChatIdThrows(): void
    {
        $this->expectException(ValidationError::class);
        $f = self::listFixture();
        unset($f['chatId']);
        NewsletterListItem::fromArray($f);
    }

    public function testListItemFromArrayWrongTypeForChatIdThrows(): void
    {
        $this->expectException(ValidationError::class);
        $f = self::listFixture();
        $f['chatId'] = 123;
        NewsletterListItem::fromArray($f);
    }
}
