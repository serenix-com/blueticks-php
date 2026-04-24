<?php

declare(strict_types=1);

namespace Blueticks\Resources;

use Blueticks\BaseResource;

final class GroupsResource extends BaseResource
{
    /**
     * @param list<string> $participants
     * @return array<string, mixed>
     */
    public function create(string $name, array $participants): array
    {
        return $this->client->request(
            'POST',
            '/v1/groups',
            ['body' => ['name' => $name, 'participants' => $participants]],
        );
    }

    /** @return array<string, mixed> */
    public function get(string $groupId): array
    {
        return $this->client->request('GET', '/v1/groups/' . rawurlencode($groupId));
    }

    /**
     * @param array<string, mixed> $opts Accepts: name, settings (array<string, bool>)
     * @return array<string, mixed>
     */
    public function update(string $groupId, array $opts): array
    {
        $body = [];
        if (isset($opts['name'])) {
            $body['name'] = $opts['name'];
        }
        if (isset($opts['settings'])) {
            $body['settings'] = $opts['settings'];
        }
        return $this->client->request(
            'PATCH',
            '/v1/groups/' . rawurlencode($groupId),
            ['body' => $body],
        );
    }

    /** @return array<string, mixed> */
    public function addMember(string $groupId, string $chatId): array
    {
        return $this->client->request(
            'POST',
            '/v1/groups/' . rawurlencode($groupId) . '/members',
            ['body' => ['chat_id' => $chatId]],
        );
    }

    /** @return array<string, mixed> */
    public function removeMember(string $groupId, string $chatId): array
    {
        return $this->client->request(
            'DELETE',
            '/v1/groups/' . rawurlencode($groupId) . '/members/' . rawurlencode($chatId),
        );
    }

    /** @return array<string, mixed> */
    public function promoteAdmin(string $groupId, string $chatId): array
    {
        return $this->client->request(
            'POST',
            '/v1/groups/' . rawurlencode($groupId) . '/members/' . rawurlencode($chatId) . '/admin',
        );
    }

    /** @return array<string, mixed> */
    public function demoteAdmin(string $groupId, string $chatId): array
    {
        return $this->client->request(
            'DELETE',
            '/v1/groups/' . rawurlencode($groupId) . '/members/' . rawurlencode($chatId) . '/admin',
        );
    }

    /**
     * @param array<string, mixed> $opts Accepts: file_data_url (required), file_name, file_mime_type
     * @return array<string, mixed>
     */
    public function setPicture(string $groupId, array $opts): array
    {
        $body = [];
        foreach (['file_data_url', 'file_name', 'file_mime_type'] as $k) {
            if (array_key_exists($k, $opts)) {
                $body[$k] = $opts[$k];
            }
        }
        return $this->client->request(
            'PUT',
            '/v1/groups/' . rawurlencode($groupId) . '/picture',
            ['body' => $body],
        );
    }

    /** @return array<string, mixed> */
    public function leave(string $groupId): array
    {
        return $this->client->request(
            'DELETE',
            '/v1/groups/' . rawurlencode($groupId) . '/members/me',
        );
    }
}
