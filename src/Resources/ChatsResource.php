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
use Blueticks\Types\MediaUrlResponse;
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

    /** Retrieve a chat by its JID. */
    public function get(string $chatId): Chat
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
     *   - mode ('latest'|'history')
     *   - query (free-text search)
     *   - since / until (ISO 8601 date-time bounds)
     *   - limit / cursor (pagination)
     *   - message_types: list<string> of allowed message kinds (e.g.
     *     ['document'] for PDFs). When omitted, server-side default-excludes
     *     system events (gp2/revoked/newsletter_notification).
     * @return Page<ChatMessage>
     */
    public function listMessages(string $chatId, array $opts = []): Page
    {
        $q = ['mode' => $opts['mode'] ?? 'latest'];
        foreach (['query', 'since', 'until', 'limit', 'cursor'] as $k) {
            if (array_key_exists($k, $opts)) {
                $q[$k] = $opts[$k];
            }
        }
        // message_types: server accepts comma-separated form for OpenAPI
        // `style: form, explode: false`. Each item must be a valid message
        // kind (chat/image/video/document/audio/ptt/sticker/gif/ptv/
        // poll_creation/location/vcard/revoked).
        if (
            array_key_exists('message_types', $opts)
            && is_array($opts['message_types'])
            && $opts['message_types'] !== []
        ) {
            $q['message_types'] = implode(',', array_map('strval', $opts['message_types']));
        }
        $raw = $this->client->request(
            'GET',
            '/v1/chats/' . rawurlencode($chatId) . '/messages',
            ['query' => $q],
        );
        return Page::fromArray($raw, fn (array $r): ChatMessage => ChatMessage::fromArray($r));
    }

    /** Retrieve a single message by WhatsApp message key. */
    public function getMessage(string $chatId, string $key): ChatMessage
    {
        $raw = $this->client->request(
            'GET',
            '/v1/chats/' . rawurlencode($chatId) . '/messages/' . rawurlencode($key),
        );
        return ChatMessage::fromArray($raw);
    }

    /** Retrieve a single message's delivery status. */
    public function getMessageAck(string $chatId, string $key): MessageAck
    {
        $raw = $this->client->request(
            'GET',
            '/v1/chats/' . rawurlencode($chatId) . '/messages/' . rawurlencode($key) . '/ack',
        );
        return MessageAck::fromArray($raw);
    }

    /** React to a message with an emoji. Empty string clears any reaction. */
    public function react(string $chatId, string $key, string $emoji): OkResponse
    {
        $raw = $this->client->request(
            'POST',
            '/v1/chats/' . rawurlencode($chatId) . '/messages/' . rawurlencode($key) . '/reactions',
            ['body' => ['emoji' => $emoji]],
        );
        return OkResponse::fromArray($raw);
    }

    /** Ask the engine to load older messages from the phone for this chat. */
    public function loadOlderMessages(string $chatId): LoadOlderMessagesResponse
    {
        $raw = $this->client->request(
            'POST',
            '/v1/chats/' . rawurlencode($chatId) . '/messages/load_older',
        );
        return LoadOlderMessagesResponse::fromArray($raw);
    }

    /** Download message media (may be returned as base64). */
    public function getMedia(string $chatId, string $key): ChatMedia
    {
        $raw = $this->client->request(
            'GET',
            '/v1/chats/' . rawurlencode($chatId) . '/messages/' . rawurlencode($key) . '/media',
        );
        return ChatMedia::fromArray($raw);
    }

    /** Get a hosted URL for the media bytes of a message. */
    public function getMediaUrl(string $chatId, string $key): MediaUrlResponse
    {
        $raw = $this->client->request(
            'GET',
            '/v1/chats/' . rawurlencode($chatId) . '/messages/' . rawurlencode($key) . '/media_url',
        );
        return MediaUrlResponse::fromArray($raw);
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
            '/v1/chats/message_acks',
            ['body' => ['message_keys' => $messageKeys]],
        );
        return BatchMessageAcksResponse::fromArray($raw);
    }
}
