<?php

declare(strict_types=1);

namespace Blueticks\Tests;

use Blueticks\Blueticks;
use Blueticks\Errors\BluetickError;
use Blueticks\Tests\Helpers\MockTransport;
use PHPUnit\Framework\TestCase;

final class BluetickClientTest extends TestCase
{
    protected function setUp(): void
    {
        putenv('BLUETICKS_API_KEY');
        putenv('BLUETICKS_BASE_URL');
    }

    public function testConstructorAcceptsApiKey(): void
    {
        $client = new Blueticks(['apiKey' => 'bt_live_x']);
        self::assertInstanceOf(Blueticks::class, $client);
    }

    public function testMissingApiKeyThrows(): void
    {
        $this->expectException(BluetickError::class);
        new Blueticks();
    }

    public function testApiKeyFromEnv(): void
    {
        putenv('BLUETICKS_API_KEY=bt_env_abc');
        $client = new Blueticks();
        self::assertInstanceOf(Blueticks::class, $client);
    }

    public function testBaseUrlPrecedenceExplicitBeatsEnvBeatsDefault(): void
    {
        putenv('BLUETICKS_API_KEY=bt_env');
        putenv('BLUETICKS_BASE_URL=https://env.example');

        $mock = new MockTransport();
        $mock->enqueueJson(200, []);

        $client = new Blueticks([
            'baseUrl' => 'https://explicit.example',
            'httpClient' => $mock->client(),
            'requestFactory' => $mock->factories(),
            'streamFactory' => $mock->factories(),
            'retryBaseMs' => 0,
            'retryCapMs' => 0,
            'sleeper' => function (int $_ms): void {
            },
        ]);
        $client->request('GET', '/v1/ping');

        self::assertStringStartsWith('https://explicit.example/v1/ping', (string) $mock->requests()[0]->getUri());
    }

    public function testBaseUrlFromEnvWhenNoExplicit(): void
    {
        putenv('BLUETICKS_API_KEY=bt_env');
        putenv('BLUETICKS_BASE_URL=https://env.example');

        $mock = new MockTransport();
        $mock->enqueueJson(200, []);

        $client = new Blueticks([
            'httpClient' => $mock->client(),
            'requestFactory' => $mock->factories(),
            'streamFactory' => $mock->factories(),
            'retryBaseMs' => 0,
            'retryCapMs' => 0,
            'sleeper' => function (int $_ms): void {
            },
        ]);
        $client->request('GET', '/v1/ping');

        self::assertStringStartsWith('https://env.example/v1/ping', (string) $mock->requests()[0]->getUri());
    }

    public function testUserAgentSuffixPlumbsThrough(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, []);

        $client = new Blueticks([
            'apiKey' => 'bt_x',
            'userAgent' => 'MyApp/1.0',
            'httpClient' => $mock->client(),
            'requestFactory' => $mock->factories(),
            'streamFactory' => $mock->factories(),
            'retryBaseMs' => 0,
            'retryCapMs' => 0,
            'sleeper' => function (int $_ms): void {
            },
        ]);
        $client->request('GET', '/v1/ping');

        self::assertStringEndsWith('MyApp/1.0', $mock->requests()[0]->getHeaderLine('User-Agent'));
    }
}
