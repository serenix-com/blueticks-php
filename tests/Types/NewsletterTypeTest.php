<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\Newsletter;
use PHPUnit\Framework\TestCase;

final class NewsletterTypeTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function fixture(): array
    {
        return [
            'id'           => '120363201733549020@newsletter',
            'name'         => 'My Channel',
            'description'  => 'Weekly updates',
            'owner'        => '15551234567@s.whatsapp.net',
            'createdAt'   => '2024-01-15T10:00:00Z',
            'subscribers'  => 42,
            'invite'       => 'abc123def456',
            'verification' => 'UNVERIFIED',
        ];
    }

    public function testFromArrayHappyPath(): void
    {
        $nl = Newsletter::fromArray(self::fixture());
        self::assertSame('120363201733549020@newsletter', $nl->id);
        self::assertSame('My Channel', $nl->name);
        self::assertSame('Weekly updates', $nl->description);
        self::assertSame('15551234567@s.whatsapp.net', $nl->owner);
        self::assertSame('2024-01-15T10:00:00Z', $nl->createdAt);
        self::assertSame(42, $nl->subscribers);
        self::assertSame('abc123def456', $nl->invite);
        self::assertSame('UNVERIFIED', $nl->verification);
    }

    public function testFromArrayNullableFieldsAreAllowedNull(): void
    {
        $f = self::fixture();
        $f['description']  = null;
        $f['owner']        = null;
        $f['createdAt']   = null;
        $f['subscribers']  = null;
        $f['invite']       = null;
        $f['verification'] = null;
        $nl = Newsletter::fromArray($f);
        self::assertNull($nl->description);
        self::assertNull($nl->owner);
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

    public function testFromArrayMissingRequiredIdThrows(): void
    {
        $this->expectException(ValidationError::class);
        $f = self::fixture();
        unset($f['id']);
        Newsletter::fromArray($f);
    }

    public function testFromArrayWrongTypeForIdThrows(): void
    {
        $this->expectException(ValidationError::class);
        $f = self::fixture();
        $f['id'] = 123;
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
        self::assertSame('120363201733549020@newsletter', $nl->id);
    }
}
