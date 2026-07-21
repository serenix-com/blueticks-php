<?php

declare(strict_types=1);

namespace Blueticks\Resources;

use Blueticks\BaseResource;
use Blueticks\Errors\ValidationError;
use Blueticks\Types\Group;
use Blueticks\Types\Page;

/**
 * WhatsApp group operations.
 *
 * Each method maps to a /v1/groups/* endpoint and returns the
 * authoritative {@see Group} snapshot the engine produced (or void
 * for `leave`, which is fire-and-forget — 204 with no body).
 */
final class GroupsResource extends BaseResource
{
    /**
     * List groups. Offset-paginated via `limit` + `skip`.
     *
     * @param array<string, mixed> $params Accepts: searchToken, includeArchive, skip, limit.
     * @return Page<Group>
     */
    public function list(array $params = []): Page
    {
        $query = self::pickQuery($params, ['searchToken', 'includeArchive', 'skip', 'limit']);
        $env = $this->client->request('GET', '/v1/groups', $query !== [] ? ['query' => $query] : []);
        return Page::fromArray($env, fn (array $row): Group => Group::fromArray($row));
    }

    /**
     * Create group.
     *
     * @param list<string> $participants Each entry is a chat id (`1234@c.us`) or phone number.
     */
    public function create(string $name, array $participants): Group
    {
        $env = $this->client->request(
            'POST',
            '/v1/groups',
            ['body' => ['name' => $name, 'participants' => $participants]],
        );
        return Group::fromArray(self::unwrap($env));
    }

    /**
     * Get group.
     *
     * Retrieve a group by its id. Pass `include=participants` to embed the
     * participant list.
     *
     * @param array<string, mixed> $params Accepts: include (`participants`).
     */
    public function retrieve(string $groupId, array $params = []): Group
    {
        $query = self::pickQuery($params, ['include']);
        $env = $this->client->request(
            'GET',
            '/v1/groups/' . rawurlencode($groupId),
            $query !== [] ? ['query' => $query] : [],
        );
        return Group::fromArray(self::unwrap($env));
    }

    /**
     * Update group. Pass any subset of `name` and `settings`.
     *
     * @param array<string, mixed> $params Accepts:
     *   - name: string
     *   - settings: array{announce?: bool, restrict?: bool,
     *     editInfoAdminsOnly?: bool, description?: string}
     */
    public function update(string $groupId, array $params): Group
    {
        $body = [];
        foreach (['name', 'settings'] as $k) {
            if (array_key_exists($k, $params)) {
                $body[$k] = $params[$k];
            }
        }
        $env = $this->client->request(
            'PATCH',
            '/v1/groups/' . rawurlencode($groupId),
            ['body' => $body],
        );
        return Group::fromArray(self::unwrap($env));
    }

    /**
     * Add member to group.
     */
    public function addMember(string $groupId, string $chatId): Group
    {
        $env = $this->client->request(
            'POST',
            '/v1/groups/' . rawurlencode($groupId) . '/members',
            ['body' => ['chatId' => $chatId]],
        );
        return Group::fromArray(self::unwrap($env));
    }

    /**
     * Remove member from group.
     */
    public function removeMember(string $groupId, string $chatId): Group
    {
        $env = $this->client->request(
            'DELETE',
            '/v1/groups/' . rawurlencode($groupId) . '/members/' . rawurlencode($chatId),
        );
        return Group::fromArray(self::unwrap($env));
    }

    /**
     * Promote member to admin.
     */
    public function promoteAdmin(string $groupId, string $chatId): Group
    {
        $env = $this->client->request(
            'POST',
            '/v1/groups/' . rawurlencode($groupId) . '/members/' . rawurlencode($chatId) . '/admin',
        );
        return Group::fromArray(self::unwrap($env));
    }

    /**
     * Demote admin to member.
     */
    public function demoteAdmin(string $groupId, string $chatId): Group
    {
        $env = $this->client->request(
            'DELETE',
            '/v1/groups/' . rawurlencode($groupId) . '/members/' . rawurlencode($chatId) . '/admin',
        );
        return Group::fromArray(self::unwrap($env));
    }

    /**
     * Set group picture.
     *
     * @param array<string, mixed> $params Accepts:
     *   - fileDataUrl: string — base64 data URL, PNG/JPEG, ≤20 MiB
     *   - url: string — https URL of the image
     *   - fileName: string
     *   - fileMimeType: string
     */
    public function setPicture(string $groupId, array $params): Group
    {
        $body = [];
        foreach (['fileDataUrl', 'url', 'fileName', 'fileMimeType'] as $k) {
            if (array_key_exists($k, $params)) {
                $body[$k] = $params[$k];
            }
        }
        $env = $this->client->request(
            'PUT',
            '/v1/groups/' . rawurlencode($groupId) . '/picture',
            ['body' => $body],
        );
        return Group::fromArray(self::unwrap($env));
    }

    /**
     * Leave group.
     *
     * Leave the group as the authenticated identity. Idempotent — succeeds
     * with 204 even if already not a member. Returns no payload.
     */
    public function leave(string $groupId): void
    {
        $this->client->request(
            'DELETE',
            '/v1/groups/' . rawurlencode($groupId) . '/members/me',
        );
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
