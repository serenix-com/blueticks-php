<?php

declare(strict_types=1);

namespace Blueticks\Resources;

use Blueticks\BaseResource;

final class EnginesResource extends BaseResource
{
    /**
     * Get engine status.
     *
     * Inspect or control the WhatsApp engine connected to the workspace.
     *
     * @return array<string, mixed>
     */
    public function retrieve(): array
    {
        return $this->client->request('GET', '/v1/engines');
    }
}
