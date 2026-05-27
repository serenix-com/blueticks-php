<?php

declare(strict_types=1);

namespace Blueticks\Resources;

use Blueticks\BaseResource;
use Blueticks\Types\Message;
use Blueticks\Types\Page;

final class MessagesResource extends BaseResource
{
    /**
     * Send message.
     *
     * Send a message via WhatsApp. The body is a discriminated union — set the
     * `type` field to one of `text`, `media`, or `poll`.
     *
     * @param array<string, mixed> $params Must include `type` (`text`|`media`|`poll`) and
     *   `to`. Type-specific required fields: `text` for text; `media` (array with
     *   `url`) for media; `poll` (array with `question` + `options`) for poll.
     *   Optional shared fields: `send_at`, `from`, `reply_to`.
     *   Pass `idempotency_key` to set the Idempotency-Key header.
     */
    public function send(array $params): Message
    {
        $requestOpts = [];
        $body = $params;

        if (isset($body['idempotency_key']) && is_string($body['idempotency_key'])) {
            $requestOpts['headers'] = ['Idempotency-Key' => $body['idempotency_key']];
            unset($body['idempotency_key']);
        }

        $requestOpts['body'] = $body;

        $raw = $this->client->request('POST', '/v1/messages', $requestOpts);
        return Message::fromArray($raw);
    }

    /**
     * Get message.
     *
     * Get the current status of a message by ID.
     */
    public function retrieve(string $id): Message
    {
        $raw = $this->client->request('GET', "/v1/messages/{$id}");
        return Message::fromArray($raw);
    }

    /**
     * List messages.
     *
     * List messages sent through the API, newest first (cursor-paginated).
     *
     * @return Page<Message>
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
            '/v1/messages',
            $query !== [] ? ['query' => $query] : [],
        );
        return Page::fromArray($raw, fn (array $row): Message => Message::fromArray($row));
    }

    /**
     * Update message.
     *
     * Edit a previously-queued message that has not dispatched yet. Accepts a
     * subset of `text`, `media_url`, `media_caption`, `send_at` — at least one
     * is required. Returns 400 once the message has advanced past the editable
     * window (status not in `pending`/`sending`).
     *
     * @param array<string, mixed> $params Allowed keys: `text`, `media_url`,
     *   `media_caption`, `send_at`. At least one is required.
     */
    public function update(string $id, array $params): Message
    {
        $raw = $this->client->request('PATCH', "/v1/messages/{$id}", ['body' => $params]);
        return Message::fromArray($raw);
    }
}
