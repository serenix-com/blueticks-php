<?php

declare(strict_types=1);

namespace Blueticks\Resources;

use Blueticks\BaseResource;
use Blueticks\Errors\ValidationError;
use Blueticks\Types\DeletedResource;
use Blueticks\Types\Message;
use Blueticks\Types\Page;

final class ScheduledMessagesResource extends BaseResource
{
    /**
     * List scheduled messages, newest first. Offset-paginated via `limit` + `skip`.
     *
     * @param array<string, mixed> $params Accepts: chatId, searchToken,
     *   status, order (`asc`|`desc`), skip, limit.
     * @return Page<Message>
     */
    public function list(array $params = []): Page
    {
        $query = self::pickQuery($params, ['chatId', 'searchToken', 'status', 'order', 'skip', 'limit']);
        $env = $this->client->request('GET', '/v1/scheduled-messages', $query !== [] ? ['query' => $query] : []);
        return Page::fromArray($env, fn (array $row): Message => Message::fromArray($row));
    }

    /**
     * Schedule message.
     *
     * Send or schedule a message to the chat named in the URL path. Omit
     * `sendAt` to send immediately; set it to schedule. The body is a flat
     * shape — set `type` to `text`, `media`, or `poll`.
     *
     * @param array<string, mixed> $params Must include `type`
     *   (`text`|`media`|`poll`). Optional `mentions` (list of participant
     *   JIDs to @mention in a group chat) and `recipientName` (display name
     *   shown in the scheduled-messages list). Pass `idempotency_key` to set
     *   the Idempotency-Key header (it is stripped from the body).
     */
    public function create(string $chatId, array $params): Message
    {
        $requestOpts = [];
        $body = $params;
        if (isset($body['idempotency_key']) && is_string($body['idempotency_key'])) {
            $requestOpts['headers'] = ['Idempotency-Key' => $body['idempotency_key']];
            unset($body['idempotency_key']);
        }
        $requestOpts['body'] = $body;
        $env = $this->client->request(
            'POST',
            '/v1/scheduled-messages/' . rawurlencode($chatId),
            $requestOpts,
        );
        return Message::fromArray(self::unwrap($env));
    }

    /**
     * Get scheduled message.
     *
     * Get the current status of a message by id.
     */
    public function retrieve(string $id): Message
    {
        $env = $this->client->request('GET', '/v1/scheduled-messages/' . rawurlencode($id));
        return Message::fromArray(self::unwrap($env));
    }

    /**
     * Update scheduled message.
     *
     * Edit a previously-queued message that has not dispatched yet. Accepts
     * the same flat body fields as `create` (all optional here), including
     * `mentions`.
     *
     * @param array<string, mixed> $params
     */
    public function update(string $id, array $params): Message
    {
        $env = $this->client->request(
            'PATCH',
            '/v1/scheduled-messages/' . rawurlencode($id),
            ['body' => $params],
        );
        return Message::fromArray(self::unwrap($env));
    }

    /**
     * Delete scheduled message.
     *
     * Delete a scheduled or queued message so it will not be sent.
     */
    public function delete(string $id): DeletedResource
    {
        $env = $this->client->request('DELETE', '/v1/scheduled-messages/' . rawurlencode($id));
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
