<?php

declare(strict_types=1);

namespace Blueticks\Resources;

use Blueticks\BaseResource;

/**
 * WhatsApp engine chat operations.
 *
 * Every method is a thin wrapper over a /v1/chats/* endpoint that
 * dispatches to the user's engine. Responses are raw associative
 * arrays; strong DTO wrappers are planned for a future release.
 */
final class ChatsResource extends BaseResource
{
    /** @return array<string, mixed> */
    public function list(?string $query = null, ?int $limit = null, ?string $cursor = null): array
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
        return $this->client->request('GET', '/v1/chats', $q !== [] ? ['query' => $q] : []);
    }

    /** @return array<string, mixed> */
    public function get(string $chatId): array
    {
        return $this->client->request('GET', '/v1/chats/' . rawurlencode($chatId));
    }

    /** @return array<string, mixed> */
    public function listParticipants(string $chatId, ?int $limit = null, ?string $cursor = null): array
    {
        $q = [];
        if ($limit !== null) {
            $q['limit'] = $limit;
        }
        if ($cursor !== null) {
            $q['cursor'] = $cursor;
        }
        return $this->client->request(
            'GET',
            '/v1/chats/' . rawurlencode($chatId) . '/participants',
            $q !== [] ? ['query' => $q] : [],
        );
    }

    /** @return array<string, mixed> */
    public function markRead(string $chatId): array
    {
        return $this->client->request('POST', '/v1/chats/' . rawurlencode($chatId) . '/mark_read');
    }

    /** @return array<string, mixed> */
    public function open(string $chatId): array
    {
        return $this->client->request('POST', '/v1/chats/' . rawurlencode($chatId) . '/open');
    }

    /**
     * @param array<string, mixed> $opts Accepts: mode ('latest'|'history'), query, since, until, limit, cursor
     * @return array<string, mixed>
     */
    public function listMessages(string $chatId, array $opts = []): array
    {
        $q = ['mode' => $opts['mode'] ?? 'latest'];
        foreach (['query', 'since', 'until', 'limit', 'cursor'] as $k) {
            if (array_key_exists($k, $opts)) {
                $q[$k] = $opts[$k];
            }
        }
        return $this->client->request(
            'GET',
            '/v1/chats/' . rawurlencode($chatId) . '/messages',
            ['query' => $q],
        );
    }

    /** @return array<string, mixed> */
    public function getMessage(string $chatId, string $key): array
    {
        return $this->client->request(
            'GET',
            '/v1/chats/' . rawurlencode($chatId) . '/messages/' . rawurlencode($key),
        );
    }

    /** @return array<string, mixed> */
    public function getMessageAck(string $chatId, string $key): array
    {
        return $this->client->request(
            'GET',
            '/v1/chats/' . rawurlencode($chatId) . '/messages/' . rawurlencode($key) . '/ack',
        );
    }

    /** @return array<string, mixed> */
    public function react(string $chatId, string $key, string $emoji): array
    {
        return $this->client->request(
            'POST',
            '/v1/chats/' . rawurlencode($chatId) . '/messages/' . rawurlencode($key) . '/reactions',
            ['body' => ['emoji' => $emoji]],
        );
    }

    /** @return array<string, mixed> */
    public function loadOlderMessages(string $chatId): array
    {
        return $this->client->request(
            'POST',
            '/v1/chats/' . rawurlencode($chatId) . '/messages/load_older',
        );
    }

    /** @return array<string, mixed> */
    public function getMedia(string $chatId, string $key): array
    {
        return $this->client->request(
            'GET',
            '/v1/chats/' . rawurlencode($chatId) . '/messages/' . rawurlencode($key) . '/media',
        );
    }

    /** @return array<string, mixed> */
    public function getMediaUrl(string $chatId, string $key): array
    {
        return $this->client->request(
            'GET',
            '/v1/chats/' . rawurlencode($chatId) . '/messages/' . rawurlencode($key) . '/media_url',
        );
    }

    /**
     * @param list<string> $messageKeys
     * @return array<string, mixed>
     */
    public function batchMessageAcks(array $messageKeys): array
    {
        return $this->client->request(
            'POST',
            '/v1/chats/message_acks',
            ['body' => ['message_keys' => $messageKeys]],
        );
    }
}
