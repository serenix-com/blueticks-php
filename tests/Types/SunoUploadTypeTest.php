<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\SunoUpload;
use PHPUnit\Framework\TestCase;

final class SunoUploadTypeTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function fixture(): array
    {
        return [
            'uploadId' => 'up_1',
            'status' => 'complete',
        ];
    }

    public function testHappyPath(): void
    {
        $u = SunoUpload::fromArray(self::fixture());
        self::assertSame('up_1', $u->uploadId);
        self::assertSame('complete', $u->status);
    }

    public function testMissingRequiredThrows(): void
    {
        $f = self::fixture();
        unset($f['uploadId']);
        $this->expectException(ValidationError::class);
        SunoUpload::fromArray($f);
    }

    public function testWrongTypeStatusThrows(): void
    {
        $f = self::fixture();
        $f['status'] = 42;
        $this->expectException(ValidationError::class);
        SunoUpload::fromArray($f);
    }
}
