<?php

declare(strict_types=1);

namespace Blueticks\Resources;

use Blueticks\BaseResource;
use Blueticks\Errors\ValidationError;
use Blueticks\Types\Campaign;
use Blueticks\Types\Page;

final class CampaignsResource extends BaseResource
{
    /**
     * List campaigns, newest first. Offset-paginated via `limit` + `skip`.
     *
     * @param array<string, mixed> $params Accepts: limit, skip, order (`asc`|`desc`).
     * @return Page<Campaign>
     */
    public function list(array $params = []): Page
    {
        $query = self::pickQuery($params, ['limit', 'skip', 'order']);
        $env = $this->client->request('GET', '/v1/campaigns', $query !== [] ? ['query' => $query] : []);
        return Page::fromArray($env, fn (array $row): Campaign => Campaign::fromArray($row));
    }

    /**
     * Create campaign.
     *
     * @param array<string, mixed> $params
     *   Accepts: text, mediaUrl, mediaCaption, onMissingVariable (`fail`|`skip`).
     */
    public function create(string $name, string $audienceId, array $params = []): Campaign
    {
        $body = ['name' => $name, 'audienceId' => $audienceId];
        foreach (['text', 'mediaUrl', 'mediaCaption', 'onMissingVariable'] as $k) {
            if (array_key_exists($k, $params)) {
                $body[$k] = $params[$k];
            }
        }
        $env = $this->client->request('POST', '/v1/campaigns', ['body' => $body]);
        return Campaign::fromArray(self::unwrap($env));
    }

    /**
     * Get campaign.
     *
     * Retrieve a campaign by id.
     */
    public function retrieve(string $id): Campaign
    {
        $env = $this->client->request('GET', '/v1/campaigns/' . rawurlencode($id));
        return Campaign::fromArray(self::unwrap($env));
    }

    /**
     * Pause campaign.
     */
    public function pause(string $id): Campaign
    {
        $env = $this->client->request('POST', '/v1/campaigns/' . rawurlencode($id) . '/pause');
        return Campaign::fromArray(self::unwrap($env));
    }

    /**
     * Resume campaign.
     */
    public function resume(string $id): Campaign
    {
        $env = $this->client->request('POST', '/v1/campaigns/' . rawurlencode($id) . '/resume');
        return Campaign::fromArray(self::unwrap($env));
    }

    /**
     * Cancel campaign.
     */
    public function cancel(string $id): Campaign
    {
        $env = $this->client->request('POST', '/v1/campaigns/' . rawurlencode($id) . '/cancel');
        return Campaign::fromArray(self::unwrap($env));
    }

    /**
     * @param array<string, mixed> $params
     * @param list<string>         $keys
     * @return array<string, scalar>
     */
    private static function pickQuery(array $params, array $keys): array
    {
        $query = [];
        foreach ($keys as $k) {
            if (array_key_exists($k, $params) && $params[$k] !== null && is_scalar($params[$k])) {
                $query[$k] = $params[$k];
            }
        }
        return $query;
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
