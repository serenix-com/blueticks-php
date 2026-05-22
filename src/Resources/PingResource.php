<?php

declare(strict_types=1);

namespace Blueticks\Resources;

use Blueticks\BaseResource;
use Blueticks\Types\Ping;

final class PingResource extends BaseResource
{
    /**
     * Ping.
     *
     * Probe the API: returns the account ID, key prefix, and granted scopes for the
     * authenticated API key. Useful as a connection test and to inspect what an
     * integration is allowed to do. No scope required.
     */
    public function retrieve(): Ping
    {
        $data = $this->client->request('GET', '/v1/ping');
        return Ping::fromArray($data);
    }
}
