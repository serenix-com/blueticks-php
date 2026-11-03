<?php

declare(strict_types=1);

namespace Blueticks\Tests\Resources;

use Blueticks\Blueticks;
use Blueticks\Errors\AuthenticationError;
use Blueticks\Tests\Helpers\MockTransport;
use Blueticks\Types\CreateUploadResult;
use Blueticks\Types\GenerateSongResult;
use Blueticks\Types\SongClip;
use Blueticks\Types\SunoAccount;
use PHPUnit\Framework\TestCase;

final class SunoResourceTest extends TestCase
{
    private function client(MockTransport $mock): Blueticks
    {
        return new Blueticks([
            'apiKey'         => 'bt_test_x',
            'baseUrl'        => 'https://api.blueticks.test',
            'httpClient'     => $mock->client(),
            'requestFactory' => $mock->factories(),
            'streamFactory'  => $mock->factories(),
            'retryBaseMs'    => 0,
            'retryCapMs'     => 0,
            'sleeper'        => function (int $_ms): void {
            },
        ]);
    }

    public function testAccount(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'success' => true,
            'data' => ['creditsLeft' => 120.5, 'plan' => 'Pro Plan'],
        ]);

        $acc = $this->client($mock)->suno->account();
        self::assertInstanceOf(SunoAccount::class, $acc);
        self::assertSame(120.5, $acc->creditsLeft);
        self::assertSame('Pro Plan', $acc->plan);
    }

    public function testGenerateSong(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'success' => true,
            'data' => [
                'jobId' => 'job_1',
                'clips' => [
                    ['id' => 'clip_1', 'status' => 'queued'],
                    ['id' => 'clip_2', 'status' => 'submitted'],
                ],
            ],
        ]);

        $r = $this->client($mock)->suno->generateSong(['lyrics' => 'la la', 'style' => 'pop']);
        self::assertInstanceOf(GenerateSongResult::class, $r);
        self::assertCount(2, $r->clips);
        self::assertSame('POST', $mock->requests()[0]->getMethod());
    }

    public function testGenerateSongInstrumentalWithoutLyrics(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(201, [
            'success' => true,
            'data' => ['jobId' => 'job_2', 'clips' => [['id' => 'clip_1', 'status' => 'queued']]],
        ]);

        $r = $this->client($mock)->suno->generateSong(['style' => 'ambient', 'instrumental' => true]);
        self::assertCount(1, $r->clips);
        self::assertSame('{"style":"ambient","instrumental":true}', (string) $mock->requests()[0]->getBody());
    }

    public function testGetSong(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'success' => true,
            'data' => ['id' => 'clip_1', 'status' => 'complete', 'audioUrl' => 'https://x/a.mp3'],
        ]);

        $clip = $this->client($mock)->suno->getSong('clip_1');
        self::assertInstanceOf(SongClip::class, $clip);
        self::assertSame('complete', $clip->status);
    }

    public function testCreateUpload(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'success' => true,
            'data' => ['uploadId' => 'up_1', 'status' => 'complete'],
        ]);

        $r = $this->client($mock)->suno->createUpload(['audioUrl' => 'https://x/a.mp3']);
        self::assertInstanceOf(CreateUploadResult::class, $r);
        self::assertSame('up_1', $r->uploadId);
    }

    public function testAccount401(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(401, ['error' => [
            'code' => 'authentication_required',
            'message' => 'bad key',
            'request_id' => 'req_x',
        ]]);

        try {
            $this->client($mock)->suno->account();
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertSame('req_x', $e->requestId);
        }
    }
}
