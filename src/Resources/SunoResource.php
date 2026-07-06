<?php

declare(strict_types=1);

namespace Blueticks\Resources;

use Blueticks\BaseResource;
use Blueticks\Types\SunoAccount;
use Blueticks\Types\SunoGeneration;
use Blueticks\Types\SunoSong;
use Blueticks\Types\SunoUpload;

/**
 * Suno song-generation operations.
 *
 * Each method maps to a /v1/suno/* endpoint. These endpoints wrap the
 * connected Suno account: submit generations, poll clips, upload
 * reference audio, and read remaining credits.
 */
final class SunoResource extends BaseResource
{
    /**
     * Generate song.
     *
     * Submit a song generation to Suno from `lyrics` + `style`, optionally
     * steering `vocalGender`, `weirdness`, `styleInfluence`, and a reference
     * recording (`uploadId` from uploadAudio, with `audioInfluence` controlling
     * how closely the cover follows it). Returns two clip variants — poll each
     * with getSong until `status` is `complete`. Requires `suno:write`.
     *
     * @param array<string, mixed> $opts Optional fields: negativeStyle, vocalGender
     *   (`m`|`f`), weirdness (0-1), styleInfluence (0-1), audioInfluence (0-1),
     *   instrumental (bool), model, title, uploadId, captchaToken.
     */
    public function generateSong(string $lyrics, string $style, array $opts = []): SunoGeneration
    {
        $body = ['lyrics' => $lyrics, 'style' => $style];
        foreach (
            [
                'negativeStyle', 'vocalGender', 'weirdness', 'styleInfluence', 'audioInfluence',
                'instrumental', 'model', 'title', 'uploadId', 'captchaToken',
            ] as $k
        ) {
            if (array_key_exists($k, $opts)) {
                $body[$k] = $opts[$k];
            }
        }
        $raw = $this->client->request('POST', '/v1/suno/songs', ['body' => $body]);
        return SunoGeneration::fromArray($raw);
    }

    /**
     * Get song.
     *
     * Poll a single generated clip by id. When `status` is `complete`, `audioUrl`
     * (MP3) and `imageUrl` are populated. Requires `suno:read`.
     */
    public function getSong(string $id): SunoSong
    {
        $raw = $this->client->request('GET', '/v1/suno/songs/' . rawurlencode($id));
        return SunoSong::fromArray($raw);
    }

    /**
     * Upload reference audio.
     *
     * Upload an audio recording (via `audioUrl` or base64 `audioBase64`) to Suno.
     * Returns an `uploadId` to pass to generateSong as `uploadId` to
     * cover/transform it. Max 500 MB. Requires `suno:write`.
     *
     * @param array<string, mixed> $opts Accepts: audioUrl, audioBase64, fileName.
     */
    public function uploadAudio(array $opts = []): SunoUpload
    {
        $body = [];
        foreach (['audioUrl', 'audioBase64', 'fileName'] as $k) {
            if (array_key_exists($k, $opts)) {
                $body[$k] = $opts[$k];
            }
        }
        $raw = $this->client->request('POST', '/v1/suno/uploads', ['body' => $body]);
        return SunoUpload::fromArray($raw);
    }

    /**
     * Get Suno account.
     *
     * Remaining credits, monthly usage, and plan on the connected Suno account.
     * Requires `suno:read`.
     */
    public function getAccount(): SunoAccount
    {
        $raw = $this->client->request('GET', '/v1/suno/account');
        return SunoAccount::fromArray($raw);
    }
}
