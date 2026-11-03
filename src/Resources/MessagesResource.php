<?php

declare(strict_types=1);

namespace Blueticks\Resources;

use Blueticks\BaseResource;
use Blueticks\Errors\ValidationError;
use Blueticks\Types\BatchMessageAck;
use Blueticks\Types\ChatMedia;
use Blueticks\Types\LoadOlderResult;
use Blueticks\Types\Message;
use Blueticks\Types\MessageAck;
use Blueticks\Types\Page;
use Blueticks\Types\PinnedMessage;
use Blueticks\Types\PublicMessage;
use Blueticks\Types\OkResponse;

/**
 * WhatsApp message operations — reading a chat's history, sending direct
 * (fire-and-forget) messages, delivery acks, media, pins, and reactions.
 */
final class MessagesResource extends BaseResource
{
    /**
     * List messages (all chats). Offset-paginated via `limit` + `skip`.
     *
     * @param array<string, mixed> $params Accepts: chatId, searchToken,
     *   order (`asc`|`desc`), since, until, messageTypes (list<string>),
     *   queryAny (list<string> keyword-set OR search), sender, hasMedia,
     *   mime, filenameContains, loadFromPhoneIfNeeded, includeMediaContent,
     *   skip, limit.
     * @return Page<PublicMessage>
     */
    public function list(array $params = []): Page
    {
        $query = self::pickQuery($params, [
            'chatId',
            'searchToken',
            'order',
            'since',
            'until',
            'sender',
            'hasMedia',
            'mime',
            'filenameContains',
            'loadFromPhoneIfNeeded',
            'includeMediaContent',
            'skip',
            'limit',
        ]);
        if (
            array_key_exists('messageTypes', $params)
            && is_array($params['messageTypes'])
            && $params['messageTypes'] !== []
        ) {
            $query['messageTypes'] = implode(',', array_map('strval', $params['messageTypes']));
        }
        if (array_key_exists('queryAny', $params) && is_array($params['queryAny']) && $params['queryAny'] !== []) {
            $query['queryAny'] = implode(',', array_map('strval', $params['queryAny']));
        }
        $env = $this->client->request('GET', '/v1/messages', $query !== [] ? ['query' => $query] : []);
        return Page::fromArray($env, fn (array $row): PublicMessage => PublicMessage::fromArray($row));
    }

    /**
     * Get message.
     *
     * Fetch a single message by its complete WhatsApp message key.
     *
     * @param array<string, mixed> $params Accepts: chatId.
     */
    public function retrieve(string $waMessageKey, array $params = []): PublicMessage
    {
        $query = self::pickQuery($params, ['chatId']);
        $env = $this->client->request(
            'GET',
            '/v1/messages/' . rawurlencode($waMessageKey),
            $query !== [] ? ['query' => $query] : [],
        );
        return PublicMessage::fromArray(self::unwrap($env));
    }

    /**
     * Send message.
     *
     * Send a message immediately to a specific chat. The body is the flat
     * shape shared with `POST /v1/scheduled-messages/{chatId}` minus
     * `sendAt`. Set `type` to `text`, `media`, or `poll`.
     *
     * @param array<string, mixed> $params Must include `type`
     *   (`text`|`media`|`poll`). Optional `mentions` (list of participant
     *   JIDs to @mention in a group chat). Pass `idempotency_key` to set the
     *   Idempotency-Key header (it is stripped from the body).
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
            '/v1/messages/' . rawurlencode($chatId),
            $requestOpts,
        );
        return Message::fromArray(self::unwrap($env));
    }

    /**
     * Get message delivery status.
     *
     * @param array<string, mixed> $params Accepts: chatId.
     */
    public function getAck(string $waMessageKey, array $params = []): MessageAck
    {
        $query = self::pickQuery($params, ['chatId']);
        $env = $this->client->request(
            'GET',
            '/v1/messages/ack/' . rawurlencode($waMessageKey),
            $query !== [] ? ['query' => $query] : [],
        );
        return MessageAck::fromArray(self::unwrap($env));
    }

    /**
     * Batch get message acks.
     *
     * Get delivery status for up to 200 sent messages in one call.
     *
     * @param list<string>         $messageKeys
     * @param array<string, mixed> $params Accepts: chatId.
     * @return list<BatchMessageAck>
     */
    public function batchAcks(array $messageKeys, array $params = []): array
    {
        $body = ['messageKeys' => $messageKeys];
        if (isset($params['chatId'])) {
            $body['chatId'] = $params['chatId'];
        }
        $env = $this->client->request('POST', '/v1/messages/acks', ['body' => $body]);
        $out = [];
        foreach (self::unwrapList($env) as $row) {
            $out[] = BatchMessageAck::fromArray($row);
        }
        return $out;
    }

    /**
     * Load older messages.
     *
     * Ask the engine to pull older history from the connected phone.
     * Optionally pull several pages in one call and/or stop at a target
     * depth; the result reports how far back the chat is now loaded under
     * `coverage`.
     *
     * @param array<string, mixed> $params Accepts: pages (1-10, default 1),
     *   until_date (ISO 8601 string or epoch seconds/ms).
     */
    public function loadOlder(string $chatId, array $params = []): LoadOlderResult
    {
        $requestOpts = [];
        $body = [];
        foreach (['pages', 'until_date'] as $k) {
            if (array_key_exists($k, $params) && $params[$k] !== null) {
                $body[$k] = $params[$k];
            }
        }
        if ($body !== []) {
            $requestOpts['body'] = $body;
        }
        $env = $this->client->request(
            'POST',
            '/v1/messages/load_older/' . rawurlencode($chatId),
            $requestOpts,
        );
        return LoadOlderResult::fromArray(self::unwrap($env));
    }

    /**
     * Get message media.
     *
     * Download the media attached to a message (image, video, document,
     * audio) as a hosted URL or inline base64.
     *
     * @param array<string, mixed> $params Accepts: chatId, maxAttempts.
     */
    public function getMedia(string $waMessageKey, array $params = []): ChatMedia
    {
        $query = self::pickQuery($params, ['chatId', 'maxAttempts']);
        $env = $this->client->request(
            'GET',
            '/v1/messages/media/' . rawurlencode($waMessageKey),
            $query !== [] ? ['query' => $query] : [],
        );
        return ChatMedia::fromArray(self::unwrap($env));
    }

    /**
     * Pin message.
     *
     * Pin a message to the top of its chat.
     *
     * @param array<string, mixed> $params Accepts: chatId (query),
     *   duration (body, seconds until the pin expires).
     */
    public function pin(string $waMessageKey, array $params = []): OkResponse
    {
        $opts = [];
        $query = self::pickQuery($params, ['chatId']);
        if ($query !== []) {
            $opts['query'] = $query;
        }
        if (isset($params['duration'])) {
            $opts['body'] = ['duration' => $params['duration']];
        }
        $env = $this->client->request('POST', '/v1/messages/pin/' . rawurlencode($waMessageKey), $opts);
        return OkResponse::fromArray(self::unwrap($env));
    }

    /**
     * Unpin message.
     *
     * @param array<string, mixed> $params Accepts: chatId.
     */
    public function unpin(string $waMessageKey, array $params = []): OkResponse
    {
        $query = self::pickQuery($params, ['chatId']);
        $env = $this->client->request(
            'POST',
            '/v1/messages/unpin/' . rawurlencode($waMessageKey),
            $query !== [] ? ['query' => $query] : [],
        );
        return OkResponse::fromArray(self::unwrap($env));
    }

    /**
     * List pinned messages.
     *
     * @return list<PinnedMessage>
     */
    public function getPinned(string $chatId): array
    {
        $env = $this->client->request(
            'GET',
            '/v1/messages/pinned/' . rawurlencode($chatId),
        );
        $out = [];
        foreach (self::unwrapList($env) as $row) {
            $out[] = PinnedMessage::fromArray($row);
        }
        return $out;
    }

    /**
     * React to message.
     *
     * Add or replace your reaction. Pass an empty `emoji` to remove it.
     *
     * @param array<string, mixed> $params Accepts: chatId.
     */
    public function react(string $waMessageKey, string $emoji, array $params = []): OkResponse
    {
        $opts = ['body' => ['emoji' => $emoji]];
        $query = self::pickQuery($params, ['chatId']);
        if ($query !== []) {
            $opts['query'] = $query;
        }
        $env = $this->client->request(
            'POST',
            '/v1/messages/reactions/' . rawurlencode($waMessageKey),
            $opts,
        );
        return OkResponse::fromArray(self::unwrap($env));
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
            if (!array_key_exists($k, $params) || $params[$k] === null) {
                continue;
            }
            $v = $params[$k];
            if (is_bool($v)) {
                $query[$k] = $v ? 'true' : 'false';
            } elseif (is_scalar($v)) {
                $query[$k] = $v;
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

    /**
     * @param array<string, mixed> $env
     * @return list<array<string, mixed>>
     */
    private static function unwrapList(array $env): array
    {
        $data = $env['data'] ?? null;
        if (!is_array($data)) {
            throw new ValidationError(message: 'Malformed API response: missing "data" array');
        }
        $rows = [];
        foreach ($data as $row) {
            if (!is_array($row)) {
                throw new ValidationError(message: 'Malformed API response: expected an array of objects');
            }
            /** @var array<string, mixed> $row */
            $rows[] = $row;
        }
        return $rows;
    }
}
