<?php

declare(strict_types=1);

namespace Blueticks\Tests\Types;

use Blueticks\Errors\ValidationError;
use Blueticks\Types\FindSimilarMatch;
use PHPUnit\Framework\TestCase;

final class FindSimilarMatchTypeTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function fixture(): array
    {
        return [
            'candidate_index' => 3,
            'item_id' => 'item_id_value',
            'text' => 'text_value',
            'score' => 0.5,
        ];
    }


    public function testHappyPath(): void
    {
        $x = FindSimilarMatch::fromArray(self::fixture());
        self::assertSame(self::fixture()['candidate_index'], $x->candidate_index);
        self::assertSame(self::fixture()['item_id'], $x->item_id);
        self::assertSame(self::fixture()['text'], $x->text);
    }

    public function testMissingRequiredThrows(): void
    {
        $data = self::fixture();
        unset($data['candidate_index']);
        $this->expectException(ValidationError::class);
        FindSimilarMatch::fromArray($data);
    }

    public function testWrongScalarThrows(): void
    {
        $data = self::fixture();
        $data['candidate_index'] = 'nope';
        $this->expectException(ValidationError::class);
        FindSimilarMatch::fromArray($data);
    }
}
