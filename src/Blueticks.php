<?php

declare(strict_types=1);

namespace Blueticks;

use Blueticks\Errors\BluetickError;

final class Blueticks
{
    private const DEFAULT_BASE_URL = 'https://api.blueticks.co';

    private Transport $transport;

    // REGEN-BOUNDARY: resource properties start
    // (Regenerated: resource property declarations are added/removed here.)
    // REGEN-BOUNDARY: resource properties end

    /**
     * @param array<string, mixed> $opts
     *
     * Accepted keys: apiKey, baseUrl, timeout, maxRetries, userAgent,
     * httpClient, requestFactory, streamFactory, retryBaseMs, retryCapMs, sleeper.
     */
    public function __construct(array $opts = [])
    {
        $apiKey = $opts['apiKey'] ?? self::envOrNull('BLUETICKS_API_KEY');
        if (!is_string($apiKey) || $apiKey === '') {
            throw new BluetickError(message: 'apiKey is required (pass it in constructor or set BLUETICKS_API_KEY).');
        }

        $baseUrl = $opts['baseUrl']
            ?? self::envOrNull('BLUETICKS_BASE_URL')
            ?? self::DEFAULT_BASE_URL;
        if (!is_string($baseUrl)) {
            throw new BluetickError(message: 'baseUrl must be a string');
        }

        $this->transport = new Transport([
            'apiKey'         => $apiKey,
            'baseUrl'        => $baseUrl,
            'timeout'        => $opts['timeout'] ?? 30.0,
            'maxRetries'     => $opts['maxRetries'] ?? 2,
            'userAgent'      => $opts['userAgent'] ?? null,
            'httpClient'     => $opts['httpClient'] ?? null,
            'requestFactory' => $opts['requestFactory'] ?? null,
            'streamFactory'  => $opts['streamFactory'] ?? null,
            'retryBaseMs'    => $opts['retryBaseMs'] ?? 500,
            'retryCapMs'     => $opts['retryCapMs'] ?? 8000,
            'sleeper'        => $opts['sleeper'] ?? null,
        ]);

        // REGEN-BOUNDARY: resource attachments start
        // (Regenerated: resource attachment assignments are added/removed here.)
        // REGEN-BOUNDARY: resource attachments end
    }

    /**
     * @param array{
     *   body?: array<string, mixed>|list<mixed>,
     *   query?: array<string, scalar|list<scalar>>,
     *   headers?: array<string, string>
     * } $opts
     * @return array<string, mixed>
     */
    public function request(string $method, string $path, array $opts = []): array
    {
        return $this->transport->request($method, $path, $opts);
    }

    // REGEN-BOUNDARY: inline helpers start
    // (Regenerated: e.g. `public function ping(): Types\Ping { ... }`.)
    // REGEN-BOUNDARY: inline helpers end

    private static function envOrNull(string $name): ?string
    {
        $v = getenv($name);
        return $v === false || $v === '' ? null : $v;
    }
}
