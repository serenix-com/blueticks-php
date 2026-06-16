<?php

declare(strict_types=1);

namespace Blueticks\Resources;

use Blueticks\BaseResource;
use Blueticks\Types\Newsletter;
use Blueticks\Types\Page;

final class NewslettersResource extends BaseResource
{
    /**
     * List newsletters.
     *
     * List newsletters visible to the connected WhatsApp engine. Cursor-paginated
     * via `limit` + `cursor`. Requires `newsletters:read` scope.
     *
     * @return Page<Newsletter>
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

        $data = $this->client->request('GET', '/v1/newsletters', $query !== [] ? ['query' => $query] : []);

        /** @var list<Newsletter> $items */
        $items = array_map(
            static fn (mixed $item): Newsletter => Newsletter::fromArray((array) $item),
            (array) $data['data'],
        );

        return new Page(
            data: $items,
            hasMore: (bool) $data['hasMore'],
            nextCursor: isset($data['nextCursor']) && is_string($data['nextCursor'])
                ? $data['nextCursor']
                : null,
        );
    }

    /**
     * Create newsletter.
     *
     * Create a new WhatsApp newsletter (channel). Requires `messages:write` scope
     * (newsletter creation shares the messages write budget).
     *
     * @param array<string, mixed> $params Newsletter creation parameters. Required: `name`. Optional: `description`.
     */
    public function create(array $params): Newsletter
    {
        $data = $this->client->request('POST', '/v1/newsletters', ['body' => $params]);
        return Newsletter::fromArray($data);
    }

    /**
     * Get newsletter.
     *
     * Retrieve a newsletter by its JID. Requires `newsletters:read` scope.
     */
    public function retrieve(string $id): Newsletter
    {
        $data = $this->client->request('GET', "/v1/newsletters/{$id}");
        return Newsletter::fromArray($data);
    }
}
