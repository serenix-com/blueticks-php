<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\FindSimilarResponse;
use PHPUnit\Framework\TestCase;

final class FindSimilarResponseTypeTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function fixture(): array
    {
        return [
            'matches' => [
                [
                    'candidate_index' => 3,
                    'item_id' => 'item_id_value',
                    'text' => 'text_value',
                    'score' => 0.5,
                ],
            ],
            'misses' => [0, 1],
        ];
    }


    public function testHappyPath(): void
    {
        $x = FindSimilarResponse::fromArray(self::fixture());
        self::assertCount(count(self::fixture()['matches']), $x->matches);
    }

    public function testMissingRequiredThrows(): void
    {
        $data = self::fixture();
        unset($data['matches']);
        $this->expectException(ValidationError::class);
        FindSimilarResponse::fromArray($data);
    }

    public function testWrongScalarThrows(): void
    {
        $data = self::fixture();
        $data['matches'] = 'nope';
        $this->expectException(ValidationError::class);
        FindSimilarResponse::fromArray($data);
    }
}
