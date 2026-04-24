<?php

declare(strict_types=1);

namespace Blueticks\Resources;

use Blueticks\BaseResource;

final class ContactsResource extends BaseResource
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
        return $this->client->request('GET', '/v1/contacts', $q !== [] ? ['query' => $q] : []);
    }

    /** @return array<string, mixed> */
    public function getProfilePicture(string $chatId): array
    {
        return $this->client->request(
            'GET',
            '/v1/contacts/' . rawurlencode($chatId) . '/profile_picture',
        );
    }
}
