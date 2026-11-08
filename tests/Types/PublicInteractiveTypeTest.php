<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\PublicInteractive;
use PHPUnit\Framework\TestCase;

final class PublicInteractiveTypeTest extends TestCase
{
    public function testHappyPath(): void
    {
        $i = PublicInteractive::fromArray([
            'kind' => 'buttons',
            'options' => [['id' => 'b1', 'title' => 'Yes']],
            'selectedId' => 'b1',
            'selectedTitle' => 'Yes',
            'quotedId' => 'q1',
        ]);
        self::assertSame('buttons', $i->kind);
        self::assertNotNull($i->options);
        self::assertSame('b1', $i->options[0]['id']);
        self::assertSame('b1', $i->selectedId);
        self::assertSame('q1', $i->quotedId);
    }

    public function testMissingKindThrows(): void
    {
        $this->expectException(ValidationError::class);
        PublicInteractive::fromArray(['selectedId' => 'b1']);
    }

    public function testUnknownKindThrows(): void
    {
        $this->expectException(ValidationError::class);
        PublicInteractive::fromArray(['kind' => 'carousel']);
    }

    public function testWrongOptionsTypeThrows(): void
    {
        $this->expectException(ValidationError::class);
        PublicInteractive::fromArray(['kind' => 'list', 'options' => 'x']);
    }
}
