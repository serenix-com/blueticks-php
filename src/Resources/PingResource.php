<?php

declare(strict_types=1);

namespace Blueticks\Resources;

use Blueticks\BaseResource;
use Blueticks\Types\Ping;

final class PingResource extends BaseResource
{
    /**
     * Health check.
     *
     * Returns basic info about the authenticated API key.
     */
    public function retrieve(): Ping
    {
        $data = $this->client->request('GET', '/v1/ping');
        return Ping::fromArray($data);
    }
}
