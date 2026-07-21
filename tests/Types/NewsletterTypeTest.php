<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\Newsletter;
use PHPUnit\Framework\TestCase;

final class NewsletterTypeTest extends TestCase
{
    public function testHappyPath(): void
    {
        $n = Newsletter::fromArray([
            'newsletterId' => '120@newsletter',
            'name' => 'News',
            'subscribers' => 42,
            'verification' => 'VERIFIED',
        ]);
        self::assertSame('120@newsletter', $n->newsletterId);
        self::assertSame(42, $n->subscribers);
        self::assertSame('VERIFIED', $n->verification);
    }

    public function testMissingRequiredThrows(): void
    {
        $this->expectException(ValidationError::class);
        Newsletter::fromArray(['name' => 'News']);
    }

    public function testInvalidVerificationThrows(): void
    {
        $this->expectException(ValidationError::class);
        Newsletter::fromArray(['newsletterId' => '120@newsletter', 'name' => 'News', 'verification' => 'MAYBE']);
    }
}
