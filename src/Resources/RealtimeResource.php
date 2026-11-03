<?php

declare(strict_types=1);

namespace Blueticks\Resources;

use Blueticks\BaseResource;
use Blueticks\Errors\ValidationError;
use Blueticks\Types\RealtimeTokenResponse;

/**
 * Real-time message delivery over MQTT.
 */
final class RealtimeResource extends BaseResource
{
    /**
     * Get MQTT credentials for receiving messages in real time.
     *
     * Trades your API key for an MQTT credential. Useful when there is no
     * public URL to deliver a webhook to: your process dials out and
     * subscribes.
     */
    public function token(): RealtimeTokenResponse
    {
        $env = $this->client->request('POST', '/v1/realtime/token');
        return RealtimeTokenResponse::fromArray(self::unwrap($env));
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
