<?php

declare(strict_types=1);

namespace Blueticks\Resources;

use Blueticks\BaseResource;

final class EnginesResource extends BaseResource
{
    /** @return array<string, mixed> */
    public function status(): array
    {
        return $this->client->request('GET', '/v1/engines');
    }

    /** @return array<string, mixed> */
    public function me(): array
    {
        return $this->client->request('GET', '/v1/engines/me');
    }

    /** @return array<string, mixed> */
    public function logout(): array
    {
        return $this->client->request('POST', '/v1/engines/logout');
    }

    /** @return array<string, mixed> */
    public function reload(): array
    {
        return $this->client->request('POST', '/v1/engines/reload');
    }
}
