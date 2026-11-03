<?php

declare(strict_types=1);

namespace Blueticks\Resources;

use Blueticks\BaseResource;
use Blueticks\Errors\ValidationError;
use Blueticks\Types\CreateUploadResult;
use Blueticks\Types\GenerateSongResult;
use Blueticks\Types\SongClip;
use Blueticks\Types\SunoAccount;

/**
 * Suno music-generation operations.
 */
final class SunoResource extends BaseResource
{
    /**
     * Get Suno account.
     *
     * Remaining credits, monthly usage, and plan on the connected Suno account.
     */
    public function account(): SunoAccount
    {
        $env = $this->client->request('GET', '/v1/suno/account');
        return SunoAccount::fromArray(self::unwrap($env));
    }

    /**
     * Generate song.
     *
     * Submit a song generation to Suno from `lyrics` + `style`. Returns the
     * generated clip variants — poll each by id with `getSong`.
     *
     * @param array<string, mixed> $params Required: `style`. `lyrics` is required
     *   unless `instrumental` is true.
     */
    public function generateSong(array $params): GenerateSongResult
    {
        $env = $this->client->request('POST', '/v1/suno/songs', ['body' => $params]);
        return GenerateSongResult::fromArray(self::unwrap($env));
    }

    /**
     * Get song.
     *
     * Poll a single generated clip by id.
     */
    public function getSong(string $id): SongClip
    {
        $env = $this->client->request('GET', '/v1/suno/songs/' . rawurlencode($id));
        return SongClip::fromArray(self::unwrap($env));
    }

    /**
     * Upload reference audio.
     *
     * Upload an audio recording to Suno. Returns an `uploadId` to pass to
     * `generateSong` to cover/transform it.
     *
     * @param array<string, mixed> $params Accepts: audioUrl, audioBase64, fileName.
     */
    public function createUpload(array $params): CreateUploadResult
    {
        $env = $this->client->request('POST', '/v1/suno/uploads', ['body' => $params]);
        return CreateUploadResult::fromArray(self::unwrap($env));
    }

    /**
     * @param array<string, mixed> $env
     * @return array<string, mixed>
     */
    private static function unwrap(array $env): array
    {
        $data = $env['data'] ?? null;
        if (!is_array($data)) {
            throw new ValidationError(message: 'Malformed API response: missing "data" envelope');
        }
        /** @var array<string, mixed> $data */
        return $data;
    }
}
