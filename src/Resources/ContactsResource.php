<?php

declare(strict_types=1);

namespace Blueticks\Resources;

use Blueticks\BaseResource;
use Blueticks\Errors\ValidationError;
use Blueticks\Types\Contact;
use Blueticks\Types\Group;
use Blueticks\Types\Page;

final class ContactsResource extends BaseResource
{
    /**
     * List contacts. Offset-paginated via `limit` + `skip`.
     *
     * @param array<string, mixed> $params Accepts: searchToken, includeArchive, skip, limit.
     * @return Page<Contact>
     */
    public function list(array $params = []): Page
    {
        $query = self::pickQuery($params, ['searchToken', 'includeArchive', 'skip', 'limit']);
        $env = $this->client->request('GET', '/v1/contacts', $query !== [] ? ['query' => $query] : []);
        return Page::fromArray($env, fn (array $row): Contact => Contact::fromArray($row));
    }

    /**
     * Get common groups.
     *
     * List the groups the connected account shares with this contact.
     *
     * @return list<Group>
     */
    public function commonGroups(string $contactId): array
    {
        $env = $this->client->request(
            'GET',
            '/v1/contacts/' . rawurlencode($contactId) . '/common_groups',
        );
        $out = [];
        foreach (self::unwrapList($env) as $row) {
            $out[] = Group::fromArray($row);
        }
        return $out;
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
