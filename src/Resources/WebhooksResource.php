<?php

declare(strict_types=1);

namespace Blueticks\Resources;

use Blueticks\BaseResource;
use Blueticks\Errors\ValidationError;
use Blueticks\Types\DeletedResource;
use Blueticks\Types\Page;
use Blueticks\Types\Webhook;

final class WebhooksResource extends BaseResource
{
    /**
     * List webhooks. Offset-paginated via `limit` + `skip`.
     *
     * @param array<string, mixed> $params Accepts: limit, skip, order (`asc`|`desc`).
     * @return Page<Webhook>
     */
    public function list(array $params = []): Page
    {
        $query = self::pickQuery($params, ['limit', 'skip', 'order']);
        $env = $this->client->request('GET', '/v1/webhooks', $query !== [] ? ['query' => $query] : []);
        return Page::fromArray($env, fn (array $row): Webhook => Webhook::fromArray($row));
    }

    /**
     * Create webhook.
     *
     * @param list<string> $events
     */
    public function create(string $url, array $events, ?string $description = null): Webhook
    {
        $body = ['url' => $url, 'events' => $events];
        if ($description !== null) {
            $body['description'] = $description;
        }
        $env = $this->client->request('POST', '/v1/webhooks', ['body' => $body]);
        return Webhook::fromArray(self::unwrap($env));
    }

    /**
     * Get webhook.
     */
    public function retrieve(string $id): Webhook
    {
        $env = $this->client->request('GET', '/v1/webhooks/' . rawurlencode($id));
        return Webhook::fromArray(self::unwrap($env));
    }

    /**
     * Update webhook.
     *
     * @param array<string, mixed> $params Accepts: url, events, description, status.
     */
    public function update(string $id, array $params): Webhook
    {
        $body = [];
        foreach (['url', 'events', 'description', 'status'] as $k) {
            if (array_key_exists($k, $params)) {
                $body[$k] = $params[$k];
            }
        }
        $env = $this->client->request('PATCH', '/v1/webhooks/' . rawurlencode($id), ['body' => $body]);
        return Webhook::fromArray(self::unwrap($env));
    }

    /**
     * Delete webhook.
     */
    public function delete(string $id): DeletedResource
    {
        $env = $this->client->request('DELETE', '/v1/webhooks/' . rawurlencode($id));
        return DeletedResource::fromArray(self::unwrap($env));
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
