<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Types\SendInChatRequest;
use PHPUnit\Framework\TestCase;

final class SendInChatRequestTypeTest extends TestCase
{
    public function testDiscriminatorConstantsMatchSpec(): void
    {
        self::assertSame('text', SendInChatRequest::TYPE_TEXT);
        self::assertSame('media', SendInChatRequest::TYPE_MEDIA);
        self::assertSame('poll', SendInChatRequest::TYPE_POLL);
    }

    public function testTypesListContainsAllVariants(): void
    {
        self::assertSame(
            ['text', 'media', 'poll'],
            SendInChatRequest::TYPES,
        );
    }
}
