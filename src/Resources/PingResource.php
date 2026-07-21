<?php

declare(strict_types=1);

namespace Blueticks\Resources;

use Blueticks\BaseResource;
use Blueticks\Errors\ValidationError;
use Blueticks\Types\Ping;

final class PingResource extends BaseResource
{
    /**
     * Ping.
     *
     * Health and connectivity probe. Confirms the API server is live and
     * lists the WhatsApp engines currently connected to this account.
     */
    public function retrieve(): Ping
    {
        $env = $this->client->request('GET', '/v1/ping');
        return Ping::fromArray(self::unwrap($env));
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
