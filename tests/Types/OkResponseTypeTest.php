<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\OkResponse;
use PHPUnit\Framework\TestCase;

final class OkResponseTypeTest extends TestCase
{
    public function testHappyPath(): void
    {
        $r = OkResponse::fromArray(['ok' => true]);
        self::assertTrue($r->ok);
    }

    public function testMissingOkThrows(): void
    {
        $this->expectException(ValidationError::class);
        OkResponse::fromArray([]);
    }

    public function testWrongTypeThrows(): void
    {
        $this->expectException(ValidationError::class);
        OkResponse::fromArray(['ok' => 'true']);
    }

    public function testFalseOkRejected(): void
    {
        $this->expectException(ValidationError::class);
        OkResponse::fromArray(['ok' => false]);
    }
}
