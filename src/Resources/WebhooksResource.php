<?php

declare(strict_types=1);

namespace Blueticks\Resources;

use Blueticks\BaseResource;
use Blueticks\Types\Webhook;
use Blueticks\Types\WebhookCreateResult;

final class WebhooksResource extends BaseResource
{
    /**
     * @param list<string>         $events
     */
    public function create(string $url, array $events, ?string $description = null): WebhookCreateResult
    {
        $body = ['url' => $url, 'events' => $events];
        if ($description !== null) {
            $body['description'] = $description;
        }
        $raw = $this->client->request('POST', '/v1/webhooks', ['body' => $body]);
        return WebhookCreateResult::fromArray($raw);
    }

    /**
     * @return list<Webhook>
     */
    public function list(): array
    {
        /** @var array<int|string, mixed> $raw */
        $raw = $this->client->request('GET', '/v1/webhooks');
        $items = is_array($raw['data'] ?? null) ? $raw['data'] : $raw;
        $out = [];
        foreach ($items as $row) {
            if (is_array($row)) {
                /** @var array<string, mixed> $row */
                $out[] = Webhook::fromArray($row);
            }
        }
        return $out;
    }

    public function get(string $id): Webhook
    {
        $raw = $this->client->request('GET', "/v1/webhooks/{$id}");
        return Webhook::fromArray($raw);
    }

    /**
     * @param array<string, mixed> $opts Accepts: url, events, description, status
     */
    public function update(string $id, array $opts): Webhook
    {
        $body = [];
        foreach (['url', 'events', 'description', 'status'] as $k) {
            if (array_key_exists($k, $opts)) {
                $body[$k] = $opts[$k];
            }
        }
        $raw = $this->client->request('PATCH', "/v1/webhooks/{$id}", ['body' => $body]);
        return Webhook::fromArray($raw);
    }

    public function delete(string $id): void
    {
        $this->client->request('DELETE', "/v1/webhooks/{$id}");
    }

    public function rotateSecret(string $id): WebhookCreateResult
    {
        $raw = $this->client->request('POST', "/v1/webhooks/{$id}/rotate-secret");
        return WebhookCreateResult::fromArray($raw);
    }
}
