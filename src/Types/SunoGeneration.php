<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

final class SunoGeneration
{
    /**
     * @param list<SunoSong> $clips
     */
    public function __construct(
        public readonly string $jobId,
        public readonly array $clips,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        if (!array_key_exists('jobId', $data) || !is_string($data['jobId'])) {
            throw new ValidationError(message: "Missing or non-string field 'jobId' in SunoGeneration response");
        }
        if (!array_key_exists('clips', $data) || !is_array($data['clips'])) {
            throw new ValidationError(message: "Missing or non-array field 'clips' in SunoGeneration response");
        }

        $clips = [];
        foreach ($data['clips'] as $row) {
            if (!is_array($row)) {
                throw new ValidationError(
                    message: "Each entry of 'clips' must be an object in SunoGeneration response",
                );
            }
            /** @var array<string, mixed> $row */
            $clips[] = SunoSong::fromArray($row);
        }

        return new self(
            jobId: $data['jobId'],
            clips: $clips,
        );
    }
}
