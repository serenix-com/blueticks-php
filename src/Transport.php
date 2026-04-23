<?php

declare(strict_types=1);

namespace Blueticks;

use Blueticks\Errors\APIConnectionError;
use Blueticks\Errors\APIError;
use Blueticks\Errors\AuthenticationError;
use Blueticks\Errors\BluetickError;
use Blueticks\Errors\BadRequestError;
use Blueticks\Errors\NotFoundError;
use Blueticks\Errors\PermissionDeniedError;
use Blueticks\Errors\RateLimitError;
use Http\Client\Exception\NetworkException;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class Transport
{
    private string $apiKey;
    private string $baseUrl;
    /** @phpstan-ignore-next-line property.onlyWritten (reserved for future Guzzle timeout passthrough) */
    private float $timeout;
    private int $maxRetries;
    private ?string $userAgentSuffix;
    private ClientInterface $httpClient;
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;
    private int $retryBaseMs;
    private int $retryCapMs;
    /** @var callable(int): void */
    private $sleeper;

    /**
     * @param array<string, mixed> $opts Supported keys:
     *   apiKey (string), baseUrl (string), timeout (float), maxRetries (int),
     *   userAgent (?string), httpClient (?ClientInterface),
     *   requestFactory (?RequestFactoryInterface), streamFactory (?StreamFactoryInterface),
     *   retryBaseMs (int), retryCapMs (int), sleeper (?callable(int):void)
     */
    public function __construct(array $opts)
    {
        $this->apiKey = (string) $opts['apiKey'];
        $this->baseUrl = rtrim((string) $opts['baseUrl'], '/');
        $this->timeout = isset($opts['timeout']) ? (float) $opts['timeout'] : 30.0;
        $this->maxRetries = isset($opts['maxRetries']) ? (int) $opts['maxRetries'] : 2;
        $this->userAgentSuffix = isset($opts['userAgent']) && is_string($opts['userAgent'])
            ? $opts['userAgent']
            : null;
        $httpClient = isset($opts['httpClient']) && $opts['httpClient'] instanceof ClientInterface
            ? $opts['httpClient']
            : null;
        $this->httpClient = $httpClient ?? Psr18ClientDiscovery::find();
        $requestFactory = isset($opts['requestFactory']) && $opts['requestFactory'] instanceof RequestFactoryInterface
            ? $opts['requestFactory']
            : null;
        $streamFactory = isset($opts['streamFactory']) && $opts['streamFactory'] instanceof StreamFactoryInterface
            ? $opts['streamFactory']
            : null;
        $this->requestFactory = $requestFactory ?? Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = $streamFactory ?? Psr17FactoryDiscovery::findStreamFactory();
        $this->retryBaseMs = isset($opts['retryBaseMs']) ? (int) $opts['retryBaseMs'] : 500;
        $this->retryCapMs = isset($opts['retryCapMs']) ? (int) $opts['retryCapMs'] : 8000;
        $sleeper = isset($opts['sleeper']) && is_callable($opts['sleeper']) ? $opts['sleeper'] : null;
        $this->sleeper = $sleeper ?? static function (int $ms): void {
            usleep($ms * 1000);
        };
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
        $method = strtoupper($method);
        $idempotent = $method !== 'POST' || isset($opts['headers']['Idempotency-Key']);

        $attempt = 0;
        while (true) {
            $request = $this->buildRequest($method, $path, $opts);
            try {
                $response = $this->httpClient->sendRequest($request);
            } catch (NetworkException $e) {
                if ($attempt >= $this->maxRetries) {
                    throw new APIConnectionError(message: $e->getMessage());
                }
                $this->backoff($attempt, null);
                $attempt++;
                continue;
            }

            $status = $response->getStatusCode();

            if ($status >= 200 && $status < 300) {
                return $this->decodeBody($response);
            }

            $retryable = $status === 429 || ($status >= 502 && $status <= 504);
            if ($retryable && $idempotent && $attempt < $this->maxRetries) {
                $this->backoff($attempt, $status === 429 ? $response : null);
                $attempt++;
                continue;
            }

            throw $this->mapHttpError($response);
        }
    }

    /**
     * @param array{
     *   body?: array<string, mixed>|list<mixed>,
     *   query?: array<string, scalar|list<scalar>>,
     *   headers?: array<string, string>
     * } $opts
     */
    private function buildRequest(string $method, string $path, array $opts): RequestInterface
    {
        $url = $this->baseUrl . $path;
        if (!empty($opts['query'])) {
            $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($opts['query']);
        }
        $request = $this->requestFactory->createRequest($method, $url)
            ->withHeader('Authorization', 'Bearer ' . $this->apiKey)
            ->withHeader('User-Agent', $this->userAgent())
            ->withHeader('Accept', 'application/json');

        if (isset($opts['body'])) {
            $body = (string) json_encode($opts['body'], JSON_THROW_ON_ERROR);
            $request = $request
                ->withBody($this->streamFactory->createStream($body))
                ->withHeader('Content-Type', 'application/json');
        }

        foreach ($opts['headers'] ?? [] as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        return $request;
    }

    private function userAgent(): string
    {
        $base = 'blueticks-php/' . Version::BLUETICKS_VERSION;
        return $this->userAgentSuffix !== null ? $base . ' ' . $this->userAgentSuffix : $base;
    }

    /** @return array<string, mixed> */
    private function decodeBody(ResponseInterface $response): array
    {
        $raw = (string) $response->getBody();
        if ($raw === '') {
            return [];
        }
        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new APIError(
                statusCode: $response->getStatusCode(),
                message: 'Response was not valid JSON: ' . $e->getMessage(),
                response: $response,
            );
        }
        if (!is_array($decoded)) {
            throw new APIError(
                statusCode: $response->getStatusCode(),
                message: 'Response JSON was not an object/array',
                response: $response,
            );
        }
        /** @var array<string, mixed> $decoded */
        return $decoded;
    }

    private function mapHttpError(ResponseInterface $response): BluetickError
    {
        $status = $response->getStatusCode();
        $raw = (string) $response->getBody();

        $code = null;
        $message = '';
        $requestId = null;

        $decoded = null;
        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            // fall through; envelope parsing will fail
        }

        if (is_array($decoded) && isset($decoded['error']) && is_array($decoded['error'])) {
            $envelope = $decoded['error'];
            $code = isset($envelope['code']) && is_string($envelope['code']) ? $envelope['code'] : null;
            $message = isset($envelope['message']) && is_string($envelope['message']) ? $envelope['message'] : '';
            $requestId = isset($envelope['request_id']) && is_string($envelope['request_id'])
                ? $envelope['request_id']
                : null;
        } else {
            $message = substr($raw, 0, 200);
        }

        if ($status === 401) {
            return new AuthenticationError(
                statusCode: $status,
                code: $code,
                message: $message,
                requestId: $requestId,
                response: $response,
            );
        }
        if ($status === 403) {
            return new PermissionDeniedError(
                statusCode: $status,
                code: $code,
                message: $message,
                requestId: $requestId,
                response: $response,
            );
        }
        if ($status === 404) {
            return new NotFoundError(
                statusCode: $status,
                code: $code,
                message: $message,
                requestId: $requestId,
                response: $response,
            );
        }
        if ($status === 400 || $status === 422) {
            return new BadRequestError(
                statusCode: $status,
                code: $code,
                message: $message,
                requestId: $requestId,
                response: $response,
            );
        }
        if ($status === 429) {
            return new RateLimitError(
                statusCode: $status,
                code: $code,
                message: $message,
                requestId: $requestId,
                response: $response,
                retryAfter: $this->parseRetryAfter($response),
            );
        }
        return new APIError(
            statusCode: $status,
            code: $code,
            message: $message,
            requestId: $requestId,
            response: $response,
        );
    }

    private function parseRetryAfter(ResponseInterface $response): ?int
    {
        $header = $response->getHeaderLine('Retry-After');
        if ($header === '') {
            return null;
        }
        if (ctype_digit($header)) {
            return (int) $header;
        }
        $ts = strtotime($header);
        if ($ts === false) {
            return null;
        }
        return max(0, $ts - time());
    }

    private function backoff(int $attempt, ?ResponseInterface $response): void
    {
        if ($response !== null && $response->getStatusCode() === 429) {
            $retryAfter = $this->parseRetryAfter($response);
            if ($retryAfter !== null) {
                ($this->sleeper)($retryAfter * 1000);
                return;
            }
        }
        $exp = min($this->retryCapMs, (int) ($this->retryBaseMs * (2 ** $attempt)));
        $jitter = $exp > 0 ? random_int(0, $exp) : 0;
        ($this->sleeper)($jitter);
    }
}
