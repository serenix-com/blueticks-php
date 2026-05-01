<?php

declare(strict_types=1);

namespace Blueticks\Resources;

use Blueticks\BaseResource;
use Blueticks\Types\AppendContactsResult;
use Blueticks\Types\Audience;
use Blueticks\Types\Contact;
use Blueticks\Types\DeletedResource;
use Blueticks\Types\Page;

final class AudiencesResource extends BaseResource
{
    /**
     * @param list<array{to: string, variables?: array<string, string>}> $contacts
     */
    public function create(string $name, array $contacts = []): Audience
    {
        $body = ['name' => $name];
        if ($contacts !== []) {
            $body['contacts'] = $contacts;
        }
        $raw = $this->client->request('POST', '/v1/audiences', ['body' => $body]);
        return Audience::fromArray($raw);
    }

    /**
     * List audiences, newest first. Cursor-paginated.
     *
     * @return Page<Audience>
     */
    public function list(?int $limit = null, ?string $cursor = null): Page
    {
        $query = [];
        if ($limit !== null) {
            $query['limit'] = $limit;
        }
        if ($cursor !== null) {
            $query['cursor'] = $cursor;
        }
        /** @var array<string, mixed> $raw */
        $raw = $this->client->request(
            'GET',
            '/v1/audiences',
            $query !== [] ? ['query' => $query] : [],
        );
        return Page::fromArray($raw, fn (array $row): Audience => Audience::fromArray($row));
    }

    public function get(string $id, ?int $page = null): Audience
    {
        $opts = [];
        if ($page !== null) {
            $opts['query'] = ['page' => $page];
        }
        $raw = $this->client->request('GET', "/v1/audiences/{$id}", $opts);
        return Audience::fromArray($raw);
    }

    public function update(string $id, string $name): Audience
    {
        $raw = $this->client->request('PATCH', "/v1/audiences/{$id}", ['body' => ['name' => $name]]);
        return Audience::fromArray($raw);
    }

    public function delete(string $id): DeletedResource
    {
        $raw = $this->client->request('DELETE', "/v1/audiences/{$id}");
        return DeletedResource::fromArray($raw);
    }

    /**
     * @param list<array{to: string, variables?: array<string, string>}> $contacts
     */
    public function appendContacts(string $id, array $contacts): AppendContactsResult
    {
        $raw = $this->client->request(
            'POST',
            "/v1/audiences/{$id}/contacts",
            ['body' => ['contacts' => $contacts]],
        );
        return AppendContactsResult::fromArray($raw);
    }

    /**
     * @param array<string, mixed> $opts Accepts: to, variables
     */
    public function updateContact(string $audienceId, string $contactId, array $opts): Contact
    {
        $body = [];
        foreach (['to', 'variables'] as $k) {
            if (array_key_exists($k, $opts)) {
                $body[$k] = $opts[$k];
            }
        }
        $raw = $this->client->request(
            'PATCH',
            "/v1/audiences/{$audienceId}/contacts/{$contactId}",
            ['body' => $body],
        );
        return Contact::fromArray($raw);
    }

    public function deleteContact(string $audienceId, string $contactId): void
    {
        $this->client->request('DELETE', "/v1/audiences/{$audienceId}/contacts/{$contactId}");
    }
}
