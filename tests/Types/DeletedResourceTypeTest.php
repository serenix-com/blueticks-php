<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\DeletedResource;
use PHPUnit\Framework\TestCase;

final class DeletedResourceTypeTest extends TestCase
{
    public function testHappyPath(): void
    {
        $r = DeletedResource::fromArray(['id' => 'aud_1', 'deleted' => true]);
        self::assertSame('aud_1', $r->id);
        self::assertTrue($r->deleted);
    }

    public function testMissingIdThrows(): void
    {
        $this->expectException(ValidationError::class);
        DeletedResource::fromArray(['deleted' => true]);
    }

    public function testMissingDeletedThrows(): void
    {
        $this->expectException(ValidationError::class);
        DeletedResource::fromArray(['id' => 'aud_1']);
    }

    public function testDeletedFalseThrows(): void
    {
        $this->expectException(ValidationError::class);
        DeletedResource::fromArray(['id' => 'aud_1', 'deleted' => false]);
    }

    public function testWrongIdTypeThrows(): void
    {
        $this->expectException(ValidationError::class);
        DeletedResource::fromArray(['id' => 123, 'deleted' => true]);
    }

    public function testWrongDeletedTypeThrows(): void
    {
        $this->expectException(ValidationError::class);
        DeletedResource::fromArray(['id' => 'aud_1', 'deleted' => 'true']);
    }
}
