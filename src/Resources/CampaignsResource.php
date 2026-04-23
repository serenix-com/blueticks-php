<?php

declare(strict_types=1);

namespace Blueticks\Resources;

use Blueticks\BaseResource;
use Blueticks\Types\Campaign;

final class CampaignsResource extends BaseResource
{
    /**
     * @param array<string, mixed> $opts
     *   Accepts: text, media_url, media_caption, from, on_missing_variable
     */
    public function create(string $name, string $audienceId, array $opts = []): Campaign
    {
        $body = ['name' => $name, 'audience_id' => $audienceId];
        foreach (['text', 'media_url', 'media_caption', 'from', 'on_missing_variable'] as $k) {
            if (array_key_exists($k, $opts)) {
                $body[$k] = $opts[$k];
            }
        }
        $raw = $this->client->request('POST', '/v1/campaigns', ['body' => $body]);
        return Campaign::fromArray($raw);
    }

    /**
     * @return list<Campaign>
     */
    public function list(): array
    {
        /** @var array<int|string, mixed> $raw */
        $raw = $this->client->request('GET', '/v1/campaigns');
        $items = is_array($raw['data'] ?? null) ? $raw['data'] : $raw;
        $out = [];
        foreach ($items as $row) {
            if (is_array($row)) {
                /** @var array<string, mixed> $row */
                $out[] = Campaign::fromArray($row);
            }
        }
        return $out;
    }

    public function get(string $id): Campaign
    {
        $raw = $this->client->request('GET', "/v1/campaigns/{$id}");
        return Campaign::fromArray($raw);
    }

    public function pause(string $id): Campaign
    {
        $raw = $this->client->request('POST', "/v1/campaigns/{$id}/pause");
        return Campaign::fromArray($raw);
    }

    public function resume(string $id): Campaign
    {
        $raw = $this->client->request('POST', "/v1/campaigns/{$id}/resume");
        return Campaign::fromArray($raw);
    }

    public function cancel(string $id): Campaign
    {
        $raw = $this->client->request('POST', "/v1/campaigns/{$id}/cancel");
        return Campaign::fromArray($raw);
    }
}
