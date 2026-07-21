<?php

declare(strict_types=1);

namespace Blueticks\Resources;

use Blueticks\BaseResource;
use Blueticks\Errors\ValidationError;
use Blueticks\Types\Newsletter;
use Blueticks\Types\NewsletterListItem;
use Blueticks\Types\Page;

final class NewslettersResource extends BaseResource
{
    /**
     * List newsletters. Offset-paginated via `limit` + `skip`.
     *
     * @param array<string, mixed> $params Accepts: searchToken, includeArchive, skip, limit.
     * @return Page<NewsletterListItem>
     */
    public function list(array $params = []): Page
    {
        $query = self::pickQuery($params, ['searchToken', 'includeArchive', 'skip', 'limit']);
        $env = $this->client->request('GET', '/v1/newsletters', $query !== [] ? ['query' => $query] : []);
        return Page::fromArray($env, fn (array $row): NewsletterListItem => NewsletterListItem::fromArray($row));
    }

    /**
     * Create newsletter.
     *
     * Create a new WhatsApp newsletter (channel).
     *
     * @param array<string, mixed> $params Required: `name`. Optional: `description`.
     */
    public function create(array $params): Newsletter
    {
        $env = $this->client->request('POST', '/v1/newsletters', ['body' => $params]);
        return Newsletter::fromArray(self::unwrap($env));
    }

    /**
     * Get newsletter.
     *
     * Retrieve a newsletter by its JID.
     */
    public function retrieve(string $id): Newsletter
    {
        $env = $this->client->request('GET', '/v1/newsletters/' . rawurlencode($id));
        return Newsletter::fromArray(self::unwrap($env));
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
