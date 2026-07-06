<?php

declare(strict_types=1);

namespace Blueticks\Tests\Resources;

use Blueticks\Blueticks;
use Blueticks\Errors\AuthenticationError;
use Blueticks\Tests\Helpers\MockTransport;
use Blueticks\Types\SunoAccount;
use Blueticks\Types\SunoGeneration;
use Blueticks\Types\SunoSong;
use Blueticks\Types\SunoUpload;
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

    /** @return array<string, mixed> */
    private static function clipFixture(string $id): array
    {
        return [
            'id' => $id,
            'status' => 'submitted',
            'audioUrl' => null,
            'imageUrl' => null,
            'title' => null,
            'durationSec' => null,
            'model' => 'v5.5',
        ];
    }

    public function testGenerateSong(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'jobId' => 'job_1',
            'clips' => [self::clipFixture('clip_a'), self::clipFixture('clip_b')],
        ]);

        $gen = $this->client($mock)->suno->generateSong(
            '[Verse]\nSunlight on the open road',
            'upbeat pop, acoustic guitar',
            ['vocalGender' => 'f', 'weirdness' => 0.4],
        );
        self::assertInstanceOf(SunoGeneration::class, $gen);
        self::assertSame('job_1', $gen->jobId);
        self::assertCount(2, $gen->clips);
        self::assertInstanceOf(SunoSong::class, $gen->clips[0]);
        self::assertSame('clip_a', $gen->clips[0]->id);

        $req = $mock->requests()[0];
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.blueticks.test/v1/suno/songs', (string) $req->getUri());
        /** @var array<string, mixed> $body */
        $body = json_decode((string) $req->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('upbeat pop, acoustic guitar', $body['style']);
        self::assertSame('f', $body['vocalGender']);
        self::assertSame(0.4, $body['weirdness']);
    }

    public function testGenerateSong401MapsToAuthenticationError(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(401, [
            'error' => [
                'code'      => 'authentication_required',
                'message'   => 'bad key',
                'requestId' => 'req_x',
            ],
        ]);

        try {
            $this->client($mock)->suno->generateSong('la la', 'pop');
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertSame(401, $e->statusCode);
            self::assertSame('authentication_required', $e->code);
            self::assertSame('req_x', $e->requestId);
        }
    }

    public function testGetSong(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'id' => 'clip_a',
            'status' => 'complete',
            'audioUrl' => 'https://cdn.suno.example/clip_a.mp3',
            'imageUrl' => 'https://cdn.suno.example/clip_a.jpg',
            'title' => 'Morning Road',
            'durationSec' => 182.5,
            'model' => 'v5.5',
        ]);

        $song = $this->client($mock)->suno->getSong('clip_a');
        self::assertInstanceOf(SunoSong::class, $song);
        self::assertSame('complete', $song->status);
        self::assertSame('https://cdn.suno.example/clip_a.mp3', $song->audioUrl);
        self::assertSame(182.5, $song->durationSec);
        self::assertSame(
            'https://api.blueticks.test/v1/suno/songs/clip_a',
            (string) $mock->requests()[0]->getUri(),
        );
    }

    public function testGetSong401MapsToAuthenticationError(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(401, [
            'error' => [
                'code'      => 'authentication_required',
                'message'   => 'bad key',
                'requestId' => 'req_x',
            ],
        ]);

        try {
            $this->client($mock)->suno->getSong('clip_a');
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertSame(401, $e->statusCode);
            self::assertSame('authentication_required', $e->code);
            self::assertSame('req_x', $e->requestId);
        }
    }

    public function testUploadAudio(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, ['uploadId' => 'up_1', 'status' => 'complete']);

        $up = $this->client($mock)->suno->uploadAudio([
            'audioUrl' => 'https://example.com/me-singing.mp3',
            'fileName' => 'me-singing.mp3',
        ]);
        self::assertInstanceOf(SunoUpload::class, $up);
        self::assertSame('up_1', $up->uploadId);
        self::assertSame('complete', $up->status);

        $req = $mock->requests()[0];
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.blueticks.test/v1/suno/uploads', (string) $req->getUri());
        /** @var array<string, mixed> $body */
        $body = json_decode((string) $req->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('https://example.com/me-singing.mp3', $body['audioUrl']);
    }

    public function testUploadAudio401MapsToAuthenticationError(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(401, [
            'error' => [
                'code'      => 'authentication_required',
                'message'   => 'bad key',
                'requestId' => 'req_x',
            ],
        ]);

        try {
            $this->client($mock)->suno->uploadAudio(['audioUrl' => 'https://example.com/x.mp3']);
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertSame(401, $e->statusCode);
            self::assertSame('authentication_required', $e->code);
            self::assertSame('req_x', $e->requestId);
        }
    }

    public function testGetAccount(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'creditsLeft' => 1200,
            'monthlyLimit' => 2500,
            'monthlyUsage' => 1300,
            'plan' => 'Pro Plan',
        ]);

        $acct = $this->client($mock)->suno->getAccount();
        self::assertInstanceOf(SunoAccount::class, $acct);
        self::assertSame(1200.0, $acct->creditsLeft);
        self::assertSame('Pro Plan', $acct->plan);
        self::assertSame(
            'https://api.blueticks.test/v1/suno/account',
            (string) $mock->requests()[0]->getUri(),
        );
    }

    public function testGetAccount401MapsToAuthenticationError(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(401, [
            'error' => [
                'code'      => 'authentication_required',
                'message'   => 'bad key',
                'requestId' => 'req_x',
            ],
        ]);

        try {
            $this->client($mock)->suno->getAccount();
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertSame(401, $e->statusCode);
            self::assertSame('authentication_required', $e->code);
            self::assertSame('req_x', $e->requestId);
        }
    }
}
