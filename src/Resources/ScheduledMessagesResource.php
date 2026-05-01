<?php

declare(strict_types=1);

namespace Blueticks\Resources;

use Blueticks\BaseResource;
use Blueticks\Types\DeletedResource;
use Blueticks\Types\Page;
use Blueticks\Types\ScheduledMessage;

final class ScheduledMessagesResource extends BaseResource
{
    /**
     * List scheduled messages.
     *
     * Retrieves a list of all scheduled-message resources from the service.
     * Cursor-paginated.
     *
     * @return Page<ScheduledMessage>
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
            '/v1/scheduled-messages',
            $query !== [] ? ['query' => $query] : [],
        );
        return Page::fromArray(
            $raw,
            fn (array $row): ScheduledMessage => ScheduledMessage::fromArray($row),
        );
    }

    /**
     * Get scheduled message.
     *
     * Retrieves a single scheduled-message resource with the given id.
     */
    public function retrieve(string $id): ScheduledMessage
    {
        $raw = $this->client->request('GET', '/v1/scheduled-messages/' . rawurlencode($id));
        return ScheduledMessage::fromArray($raw);
    }

    /**
     * Update scheduled message.
     *
     * Updates the scheduled-message resource identified by id with the
     * provided data.
     *
     * @param array<string, mixed> $params Accepts: text, media_url, media_caption, send_at
     */
    public function update(string $id, array $params = []): ScheduledMessage
    {
        $body = [];
        foreach (['text', 'media_url', 'media_caption', 'send_at'] as $k) {
            if (array_key_exists($k, $params)) {
                $body[$k] = $params[$k];
            }
        }
        $raw = $this->client->request(
            'PATCH',
            '/v1/scheduled-messages/' . rawurlencode($id),
            ['body' => $body],
        );
        return ScheduledMessage::fromArray($raw);
    }

    /**
     * Cancel scheduled message.
     *
     * Cancel a queued scheduled message before it fires. Soft-deletes the
     * row (still queryable in audit logs). Returns the deleted ref.
     */
    public function delete(string $id): DeletedResource
    {
        $raw = $this->client->request('DELETE', '/v1/scheduled-messages/' . rawurlencode($id));
        return DeletedResource::fromArray($raw);
    }
}
