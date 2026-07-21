<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * Result of submitting a Suno generation via `POST /v1/suno/songs`.
 * Carries a `jobId` and the generated {@see SongClip} variants to poll.
 */
final class GenerateSongResult
{
    /**
     * @param list<SongClip> $clips
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
            throw new ValidationError(message: "Missing or non-string field 'jobId' in GenerateSongResult response");
        }
        if (!array_key_exists('clips', $data) || !is_array($data['clips'])) {
            throw new ValidationError(message: "Missing or non-array field 'clips' in GenerateSongResult response");
        }

        $clips = [];
        foreach ($data['clips'] as $row) {
            if (!is_array($row)) {
                throw new ValidationError(
                    message: "Each entry of 'clips' must be an object in GenerateSongResult response",
                );
            }
            /** @var array<string, mixed> $row */
            $clips[] = SongClip::fromArray($row);
        }

        return new self(
            jobId: $data['jobId'],
            clips: $clips,
        );
    }
}
