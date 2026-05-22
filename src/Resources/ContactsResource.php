<?php

declare(strict_types=1);

namespace Blueticks\Resources;

use Blueticks\BaseResource;

final class ContactsResource extends BaseResource
{
    /**
     * List contacts.
     *
     * List WhatsApp contacts known to the connected engine.
     *
     * @return array<string, mixed>
     */
    public function list(?int $limit = null, ?string $cursor = null): array
    {
        $query = [];
        if ($limit !== null) {
            $query['limit'] = $limit;
        }
        if ($cursor !== null) {
            $query['cursor'] = $cursor;
        }
        return $this->client->request(
            'GET',
            '/v1/contacts',
            $query !== [] ? ['query' => $query] : [],
        );
    }
}
