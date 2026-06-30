<?php

declare(strict_types=1);

namespace Blueticks\Resources;

use Blueticks\BaseResource;
use Blueticks\Types\BatchMessageAcksResponse;
use Blueticks\Types\Chat;
use Blueticks\Types\ChatMedia;
use Blueticks\Types\ChatMessage;
use Blueticks\Types\ChatRef;
use Blueticks\Types\LoadOlderMessagesResponse;
use Blueticks\Types\Message;
use Blueticks\Types\MessageAck;
use Blueticks\Types\OkResponse;
use Blueticks\Types\Page;
use Blueticks\Types\Participant;

/**
 * WhatsApp engine chat operations.
 *
 * Every method is a thin wrapper over a /v1/chats/* endpoint that
 * dispatches to the user's engine.
 */
final class ChatsResource extends BaseResource
{
    /**
     * List/search chats, newest first. Cursor-paginated.
     *
     * @return Page<Chat>
     */
    public function list(?string $query = null, ?int $limit = null, ?string $cursor = null): Page
    {
        $q = [];
        if ($query !== null) {
            $q['query'] = $query;
        }
        if ($limit !== null) {
            $q['limit'] = $limit;
        }
        if ($cursor !== null) {
            $q['cursor'] = $cursor;
        }
        $raw = $this->client->request('GET', '/v1/chats', $q !== [] ? ['query' => $q] : []);
        return Page::fromArray($raw, fn (array $r): Chat => Chat::fromArray($r));
    }

    /**
     * Get chat.
     *
     * Retrieve a chat by its JID.
     */
    public function retrieve(string $chatId): Chat
    {
        $raw = $this->client->request('GET', '/v1/chats/' . rawurlencode($chatId));
        return Chat::fromArray($raw);
    }

    /**
     * List participants in a group chat. Cursor-paginated.
     *
     * @return Page<Participant>
     */
    public function listParticipants(string $chatId, ?int $limit = null, ?string $cursor = null): Page
    {
        $q = [];
        if ($limit !== null) {
            $q['limit'] = $limit;
        }
        if ($cursor !== null) {
            $q['cursor'] = $cursor;
        }
        $raw = $this->client->request(
            'GET',
            '/v1/chats/' . rawurlencode($chatId) . '/participants',
            $q !== [] ? ['query' => $q] : [],
        );
        return Page::fromArray($raw, fn (array $r): Participant => Participant::fromArray($r));
    }

    /** Mark a chat as read. */
    public function markRead(string $chatId): OkResponse
    {
        $raw = $this->client->request('POST', '/v1/chats/' . rawurlencode($chatId) . '/mark_read');
        return OkResponse::fromArray($raw);
    }

    /** Open a chat in the engine and return a reference to it. */
    public function open(string $chatId): ChatRef
    {
        $raw = $this->client->request('POST', '/v1/chats/' . rawurlencode($chatId) . '/open');
        return ChatRef::fromArray($raw);
    }

    /**
     * List messages in a chat.
     *
     * @param array<string, mixed> $opts Accepts:
     *   - order ('asc'|'desc') — asc = oldest-first, desc = newest-first (default)
     *   - searchToken (free-text search)
     *   - since / until (ISO 8601 date-time bounds)
     *   - loadFromPhoneIfNeeded (bool)
     *   - includeMediaContent (bool)
     *   - skip / limit (pagination)
     *   - messageTypes: list<string> of allowed message kinds (e.g.
     *     ['document'] for PDFs). When omitted, server-side default-excludes
     *     system events (gp2/revoked/newsletter_notification).
     * @return Page<ChatMessage>
     */
    public function listMessages(string $chatId, array $opts = []): Page
    {
        $q = ['chatId' => $chatId];
        foreach (
            ['order', 'searchToken', 'since', 'until', 'loadFromPhoneIfNeeded', 'includeMediaContent', 'skip', 'limit']
            as $k
        ) {
            if (array_key_exists($k, $opts)) {
                $q[$k] = $opts[$k];
            }
        }
        // messageTypes: server accepts comma-separated form for OpenAPI
        // `style: form, explode: false`. Each item must be a valid message
        // kind (chat/image/video/document/audio/ptt/sticker/gif/ptv/
        // poll_creation/location/vcard/revoked).
        if (
            array_key_exists('messageTypes', $opts)
            && is_array($opts['messageTypes'])
            && $opts['messageTypes'] !== []
        ) {
            $q['messageTypes'] = implode(',', array_map('strval', $opts['messageTypes']));
        }
        $raw = $this->client->request(
            'GET',
            '/v1/messages',
            ['query' => $q],
        );
        return Page::fromArray($raw, fn (array $r): ChatMessage => ChatMessage::fromArray($r));
    }

    /**
     * Retrieve a single message by its complete WhatsApp message key.
     *
     * @param ?string $chatId Optional chat hint; helps the engine locate the
     *   message faster when the key alone is ambiguous.
     */
    public function getMessage(string $waMessageKey, ?string $chatId = null): ChatMessage
    {
        $raw = $this->client->request(
            'GET',
            '/v1/messages/' . rawurlencode($waMessageKey),
            self::chatIdQuery($chatId),
        );
        return ChatMessage::fromArray($raw);
    }

    /**
     * Retrieve a single message's delivery status.
     *
     * @param ?string $chatId Optional chat hint.
     */
    public function getMessageAck(string $waMessageKey, ?string $chatId = null): MessageAck
    {
        $raw = $this->client->request(
            'GET',
            '/v1/messages/ack/' . rawurlencode($waMessageKey),
            self::chatIdQuery($chatId),
        );
        return MessageAck::fromArray($raw);
    }

    /**
     * React to a message with an emoji. Empty string clears any reaction.
     *
     * @param ?string $chatId Optional chat hint.
     */
    public function react(string $waMessageKey, string $emoji, ?string $chatId = null): OkResponse
    {
        $opts = ['body' => ['emoji' => $emoji]];
        if ($chatId !== null) {
            $opts['query'] = ['chatId' => $chatId];
        }
        $raw = $this->client->request(
            'POST',
            '/v1/messages/reactions/' . rawurlencode($waMessageKey),
            $opts,
        );
        return OkResponse::fromArray($raw);
    }

    /**
     * Pin a message to the top of its chat by its complete WhatsApp message key.
     * $duration is the pin expiry in seconds; pass null for WhatsApp's 7-day default.
     */
    public function pin(string $waMessageKey, ?int $duration = null, ?string $chatId = null): OkResponse
    {
        $opts = [];
        if ($duration !== null) {
            $opts['body'] = ['duration' => $duration];
        }
        if ($chatId !== null) {
            $opts['query'] = ['chatId' => $chatId];
        }
        $raw = $this->client->request(
            'POST',
            '/v1/messages/pin/' . rawurlencode($waMessageKey),
            $opts,
        );
        return OkResponse::fromArray($raw);
    }

    /** Remove an existing pin from a message by its complete WhatsApp message key. */
    public function unpin(string $waMessageKey, ?string $chatId = null): OkResponse
    {
        $opts = [];
        if ($chatId !== null) {
            $opts['query'] = ['chatId' => $chatId];
        }
        $raw = $this->client->request(
            'POST',
            '/v1/messages/unpin/' . rawurlencode($waMessageKey),
            $opts,
        );
        return OkResponse::fromArray($raw);
    }

    /** Ask the engine to load older messages from the phone for this chat. */
    public function loadOlderMessages(string $chatId): LoadOlderMessagesResponse
    {
        $raw = $this->client->request(
            'POST',
            '/v1/messages/load_older/' . rawurlencode($chatId),
        );
        return LoadOlderMessagesResponse::fromArray($raw);
    }

    /**
     * Download message media (may be returned as base64).
     *
     * @param ?string $chatId Optional chat hint.
     */
    public function getMedia(string $waMessageKey, ?string $chatId = null): ChatMedia
    {
        $raw = $this->client->request(
            'GET',
            '/v1/messages/media/' . rawurlencode($waMessageKey),
            self::chatIdQuery($chatId),
        );
        return ChatMedia::fromArray($raw);
    }

    /**
     * Send message to chat.
     *
     * Send a message immediately to a specific chat. The body is the same
     * discriminated union as `POST /v1/scheduled-messages` minus `to`
     * (derived from the URL path) and `sendAt` (fire-and-forget).
     * Variants: `text`, `media`, `poll`.
     *
     * @param array<string, mixed> $params Must include `type`
     *   (`text`|`media`|`poll`). Type-specific required fields: `text`
     *   for text; `mediaUrl` or `mediaBase64` for media; `pollQuestion` +
     *   `pollOptions` for poll. Optional shared fields: `from`, `replyTo`,
     *   `mentions`. Pass `idempotencyKey` to set the Idempotency-Key header.
     */
    public function sendMessage(string $chatId, array $params): Message
    {
        $requestOpts = [];
        $body = $params;

        if (isset($body['idempotencyKey']) && is_string($body['idempotencyKey'])) {
            $requestOpts['headers'] = ['Idempotency-Key' => $body['idempotencyKey']];
            unset($body['idempotencyKey']);
        }

        $requestOpts['body'] = $body;

        $raw = $this->client->request(
            'POST',
            '/v1/messages/' . rawurlencode($chatId),
            $requestOpts,
        );
        return Message::fromArray($raw);
    }

    /**
     * Look up delivery acks for many messages at once.
     *
     * @param list<string> $messageKeys
     */
    public function batchMessageAcks(array $messageKeys): BatchMessageAcksResponse
    {
        $raw = $this->client->request(
            'POST',
            '/v1/messages/acks',
            ['body' => ['messageKeys' => $messageKeys]],
        );
        return BatchMessageAcksResponse::fromArray($raw);
    }

    /**
     * Build the optional `chatId` query wrapper for message-key endpoints.
     *
     * @return array<string, mixed>
     */
    private static function chatIdQuery(?string $chatId): array
    {
        return $chatId !== null ? ['query' => ['chatId' => $chatId]] : [];
    }
}
