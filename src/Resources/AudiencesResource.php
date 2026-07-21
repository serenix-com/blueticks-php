<?php

declare(strict_types=1);

namespace Blueticks\Resources;

use Blueticks\BaseResource;
use Blueticks\Errors\ValidationError;
use Blueticks\Types\AppendContactsResult;
use Blueticks\Types\Audience;
use Blueticks\Types\AudienceContact;
use Blueticks\Types\DeletedResource;
use Blueticks\Types\Page;

final class AudiencesResource extends BaseResource
{
    /**
     * List audiences, newest first. Offset-paginated via `limit` + `skip`.
     *
     * @param array<string, mixed> $params Accepts: limit, skip, order (`asc`|`desc`).
     * @return Page<Audience>
     */
    public function list(array $params = []): Page
    {
        $query = self::pickQuery($params, ['limit', 'skip', 'order']);
        $env = $this->client->request('GET', '/v1/audiences', $query !== [] ? ['query' => $query] : []);
        return Page::fromArray($env, fn (array $row): Audience => Audience::fromArray($row));
    }

    /**
     * Create audience.
     *
     * @param list<array{to: string, variables?: array<string, string>}> $contacts
     */
    public function create(string $name, array $contacts = []): Audience
    {
        $body = ['name' => $name];
        if ($contacts !== []) {
            $body['contacts'] = $contacts;
        }
        $env = $this->client->request('POST', '/v1/audiences', ['body' => $body]);
        return Audience::fromArray(self::unwrap($env));
    }

    /**
     * Get audience.
     *
     * Retrieve a single audience by id.
     */
    public function retrieve(string $id): Audience
    {
        $env = $this->client->request('GET', '/v1/audiences/' . rawurlencode($id));
        return Audience::fromArray(self::unwrap($env));
    }

    /**
     * Update audience.
     */
    public function update(string $id, string $name): Audience
    {
        $env = $this->client->request(
            'PATCH',
            '/v1/audiences/' . rawurlencode($id),
            ['body' => ['name' => $name]],
        );
        return Audience::fromArray(self::unwrap($env));
    }

    /**
     * Delete audience.
     */
    public function delete(string $id): DeletedResource
    {
        $env = $this->client->request('DELETE', '/v1/audiences/' . rawurlencode($id));
        return DeletedResource::fromArray(self::unwrap($env));
    }

    /**
     * Append contacts to audience.
     *
     * @param list<array{to: string, variables?: array<string, string>}> $contacts
     */
    public function appendContacts(string $id, array $contacts): AppendContactsResult
    {
        $env = $this->client->request(
            'POST',
            '/v1/audiences/' . rawurlencode($id) . '/contacts',
            ['body' => ['contacts' => $contacts]],
        );
        return AppendContactsResult::fromArray(self::unwrap($env));
    }

    /**
     * Update audience contact.
     *
     * @param array<string, mixed> $params Accepts: to, variables.
     */
    public function updateContact(string $audienceId, string $contactId, array $params): AudienceContact
    {
        $body = [];
        foreach (['to', 'variables'] as $k) {
            if (array_key_exists($k, $params)) {
                $body[$k] = $params[$k];
            }
        }
        $env = $this->client->request(
            'PATCH',
            '/v1/audiences/' . rawurlencode($audienceId) . '/contacts/' . rawurlencode($contactId),
            ['body' => $body],
        );
        return AudienceContact::fromArray(self::unwrap($env));
    }

    /**
     * Remove audience contact.
     */
    public function deleteContact(string $audienceId, string $contactId): void
    {
        $this->client->request(
            'DELETE',
            '/v1/audiences/' . rawurlencode($audienceId) . '/contacts/' . rawurlencode($contactId),
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
            if (array_key_exists($k, $params) && $params[$k] !== null && is_scalar($params[$k])) {
                $query[$k] = $params[$k];
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
