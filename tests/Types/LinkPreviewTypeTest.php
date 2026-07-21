<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\LinkPreview;
use PHPUnit\Framework\TestCase;

final class LinkPreviewTypeTest extends TestCase
{
    public function testHappyPath(): void
    {
        $lp = LinkPreview::fromArray([
            'title' => 'Example',
            'description' => 'desc',
            'canonicalUrl' => 'https://example.com',
            'thumbnail' => 'data:image/png;base64,AAA',
        ]);
        self::assertSame('Example', $lp->title);
        self::assertSame('https://example.com', $lp->canonicalUrl);
    }

    public function testAllFieldsOptional(): void
    {
        $lp = LinkPreview::fromArray([]);
        self::assertNull($lp->title);
    }

    public function testWrongScalarThrows(): void
    {
        $this->expectException(ValidationError::class);
        LinkPreview::fromArray(['title' => 123]);
    }
}
