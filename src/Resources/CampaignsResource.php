<?php

declare(strict_types=1);

namespace Blueticks\Resources;

use Blueticks\BaseResource;
use Blueticks\Types\Campaign;
use Blueticks\Types\Page;

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
     * List campaigns, newest first. Cursor-paginated.
     *
     * @return Page<Campaign>
     */
    public function list(?int $limit = null, ?string $cursor = null): Page
    {
        $query = [];
        if ($limit !== null) {
            $query['limit'] = $limit;
        }
        if ($cursor !== null) {
            $query['cursor'] = $cursor;
        }
        /** @var array<string, mixed> $raw */
        $raw = $this->client->request(
            'GET',
            '/v1/campaigns',
            $query !== [] ? ['query' => $query] : [],
        );
        return Page::fromArray($raw, fn (array $row): Campaign => Campaign::fromArray($row));
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
