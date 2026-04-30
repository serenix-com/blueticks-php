<?php

declare(strict_types=1);

namespace Blueticks\Tests\Helpers;

use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use Http\Mock\Client as MockHttpClient;
use Psr\Http\Message\RequestInterface;

/**
 * Test helper: wraps php-http/mock-client with ergonomic queueing.
 *
 * Usage:
 *   $mock = new MockTransport();
 *   $mock->enqueueJson(200, ['ok' => true]);
 *   $client = new Blueticks([
 *       'apiKey' => 'bt_test_x',
 *       'httpClient' => $mock->client(),
 *       'requestFactory' => $mock->factories(),
 *       'streamFactory' => $mock->factories(),
 *   ]);
 */
final class MockTransport
{
    private MockHttpClient $client;
    private HttpFactory $factories;

    public function __construct()
    {
        $this->client = new MockHttpClient();
        $this->factories = new HttpFactory();
    }

    public function client(): MockHttpClient
    {
        return $this->client;
    }

    public function factories(): HttpFactory
    {
        return $this->factories;
    }

    /**
     * Enqueue a JSON response with the given status and body.
     *
     * @param array<string, mixed>|list<mixed> $body
     * @param array<string, string>            $headers
     */
    public function enqueueJson(int $status, array $body, array $headers = []): void
    {
        $this->client->addResponse(new Response(
            $status,
            array_merge(['Content-Type' => 'application/json'], $headers),
            (string) json_encode($body, JSON_THROW_ON_ERROR),
        ));
    }

    /**
     * Enqueue a raw-body response (for malformed-JSON / non-envelope tests).
     *
     * @param array<string, string> $headers
     */
    public function enqueueRaw(int $status, string $body, array $headers = []): void
    {
        $this->client->addResponse(new Response($status, $headers, $body));
    }

    /** Enqueue a network failure for the next request. */
    public function enqueueNetworkError(\Exception $e): void
    {
        $this->client->addException($e);
    }

    /** @return list<RequestInterface> */
    public function requests(): array
    {
        return array_values($this->client->getRequests());
    }
}
