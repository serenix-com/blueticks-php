<?php

declare(strict_types=1);

namespace Blueticks\Resources;

use Blueticks\BaseResource;
use Blueticks\Errors\ValidationError;
use Blueticks\Types\Chat;
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
     * List chats, most-recent first. Offset-paginated via `limit` + `skip`.
     *
     * @param array<string, mixed> $params Accepts: searchToken,
     *   filter (`groups`|`contacts`|`newsletters`),
     *   kinds (list of `contact`|`group`|`newsletter`),
     *   since (ISO 8601 instant; chats with a last message at/after it),
     *   includeLastMessage,
     *   includeExtendedInfo, includeWithoutName, includeArchive, skip, limit.
     * @return Page<Chat>
     */
    public function list(array $params = []): Page
    {
        $query = self::pickQuery($params, [
            'searchToken',
            'filter',
            'since',
            'includeLastMessage',
            'includeExtendedInfo',
            'includeWithoutName',
            'includeArchive',
            'skip',
            'limit',
        ]);
        if (array_key_exists('kinds', $params) && is_array($params['kinds']) && $params['kinds'] !== []) {
            $query['kinds'] = implode(',', array_map('strval', $params['kinds']));
        }
        $env = $this->client->request('GET', '/v1/chats', $query !== [] ? ['query' => $query] : []);
        return Page::fromArray($env, fn (array $row): Chat => Chat::fromArray($row));
    }

    /**
     * Get chat.
     *
     * Retrieve a single chat by its id (phone number or WhatsApp JID).
     */
    public function retrieve(string $id): Chat
    {
        $env = $this->client->request('GET', '/v1/chats/' . rawurlencode($id));
        return Chat::fromArray(self::unwrap($env));
    }

    /**
     * List chat participants. Offset-paginated via `limit` + `skip`.
     *
     * @param array<string, mixed> $params Accepts: searchToken, skip, limit.
     * @return Page<Participant>
     */
    public function listParticipants(string $chatId, array $params = []): Page
    {
        $query = self::pickQuery($params, ['searchToken', 'skip', 'limit']);
        $env = $this->client->request(
            'GET',
            '/v1/chats/' . rawurlencode($chatId) . '/participants',
            $query !== [] ? ['query' => $query] : [],
        );
        return Page::fromArray($env, fn (array $row): Participant => Participant::fromArray($row));
    }

    /**
     * Archive chat.
     */
    public function archive(string $chatId): OkResponse
    {
        $env = $this->client->request('POST', '/v1/chats/' . rawurlencode($chatId) . '/archive');
        return OkResponse::fromArray(self::unwrap($env));
    }

    /**
     * Unarchive chat.
     */
    public function unarchive(string $chatId): OkResponse
    {
        $env = $this->client->request('POST', '/v1/chats/' . rawurlencode($chatId) . '/unarchive');
        return OkResponse::fromArray(self::unwrap($env));
    }

    /**
     * Mark chat as read.
     */
    public function markRead(string $chatId): OkResponse
    {
        $env = $this->client->request('POST', '/v1/chats/' . rawurlencode($chatId) . '/mark_read');
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
}
