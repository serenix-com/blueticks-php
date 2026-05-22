<?php

declare(strict_types=1);

namespace Blueticks\Resources;

use Blueticks\BaseResource;
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
     * List groups.
     *
     * List the groups the connected WhatsApp engine sees. Supports cursor
     * pagination (`limit`+`cursor`) and an optional case-insensitive substring
     * search on the group name via `q`.
     *
     * @return Page<Group>
     */
    public function list(?int $limit = null, ?string $cursor = null, ?string $q = null): Page
    {
        $query = [];
        if ($limit !== null) {
            $query['limit'] = $limit;
        }
        if ($cursor !== null) {
            $query['cursor'] = $cursor;
        }
        if ($q !== null) {
            $query['q'] = $q;
        }
        /** @var array<string, mixed> $raw */
        $raw = $this->client->request(
            'GET',
            '/v1/groups',
            $query !== [] ? ['query' => $query] : [],
        );
        return Page::fromArray($raw, fn (array $row): Group => Group::fromArray($row));
    }

    /**
     * Create a new group with the given name and initial participants.
     *
     * @param list<string> $participants Each entry is a chat id (e.g. `1234@c.us`).
     */
    public function create(string $name, array $participants): Group
    {
        $raw = $this->client->request(
            'POST',
            '/v1/groups',
            ['body' => ['name' => $name, 'participants' => $participants]],
        );
        return Group::fromArray($raw);
    }

    /**
     * Get group.
     *
     * Retrieve a group by id.
     */
    public function retrieve(string $groupId): Group
    {
        $raw = $this->client->request('GET', '/v1/groups/' . rawurlencode($groupId));
        return Group::fromArray($raw);
    }

    /**
     * Update group metadata. Pass any subset of name and settings.
     *
     * @param array<string, mixed> $opts Accepts:
     *   - name: string
     *   - settings: array{announce?: bool, restrict?: bool}
     */
    public function update(string $groupId, array $opts): Group
    {
        $body = [];
        if (isset($opts['name'])) {
            $body['name'] = $opts['name'];
        }
        if (isset($opts['settings'])) {
            $body['settings'] = $opts['settings'];
        }
        $raw = $this->client->request(
            'PATCH',
            '/v1/groups/' . rawurlencode($groupId),
            ['body' => $body],
        );
        return Group::fromArray($raw);
    }

    /** Add a member to the group. */
    public function addMember(string $groupId, string $chatId): Group
    {
        $raw = $this->client->request(
            'POST',
            '/v1/groups/' . rawurlencode($groupId) . '/members',
            ['body' => ['chat_id' => $chatId]],
        );
        return Group::fromArray($raw);
    }

    /** Remove a member from the group. */
    public function removeMember(string $groupId, string $chatId): Group
    {
        $raw = $this->client->request(
            'DELETE',
            '/v1/groups/' . rawurlencode($groupId) . '/members/' . rawurlencode($chatId),
        );
        return Group::fromArray($raw);
    }

    /** Promote a member to admin. */
    public function promoteAdmin(string $groupId, string $chatId): Group
    {
        $raw = $this->client->request(
            'POST',
            '/v1/groups/' . rawurlencode($groupId) . '/members/' . rawurlencode($chatId) . '/admin',
        );
        return Group::fromArray($raw);
    }

    /** Demote an admin back to a regular member. */
    public function demoteAdmin(string $groupId, string $chatId): Group
    {
        $raw = $this->client->request(
            'DELETE',
            '/v1/groups/' . rawurlencode($groupId) . '/members/' . rawurlencode($chatId) . '/admin',
        );
        return Group::fromArray($raw);
    }

    /**
     * Replace the group's profile picture.
     *
     * @param array<string, mixed> $opts Accepts:
     *   - file_data_url: string (required) — base64 data URL, PNG/JPEG, ≤20 MiB
     *   - file_name: string
     *   - file_mime_type: string
     */
    public function setPicture(string $groupId, array $opts): Group
    {
        $body = [];
        foreach (['file_data_url', 'file_name', 'file_mime_type'] as $k) {
            if (array_key_exists($k, $opts)) {
                $body[$k] = $opts[$k];
            }
        }
        $raw = $this->client->request(
            'PUT',
            '/v1/groups/' . rawurlencode($groupId) . '/picture',
            ['body' => $body],
        );
        return Group::fromArray($raw);
    }

    /**
     * Leave the group as the authenticated identity. Idempotent —
     * succeeds even if already not a member. Returns no payload.
     */
    public function leave(string $groupId): void
    {
        $this->client->request(
            'DELETE',
            '/v1/groups/' . rawurlencode($groupId) . '/members/me',
        );
    }
}
