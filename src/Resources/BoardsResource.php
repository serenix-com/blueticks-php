<?php

declare(strict_types=1);

namespace Blueticks\Resources;

use Blueticks\BaseResource;
use Blueticks\Errors\ValidationError;
use Blueticks\Types\BoardDetail;
use Blueticks\Types\BoardItem;
use Blueticks\Types\BoardRow;
use Blueticks\Types\BulkItemsResponse;
use Blueticks\Types\FindSimilarResponse;
use Blueticks\Types\Page;

/**
 * Boards (agent memory boards) and their items.
 */
final class BoardsResource extends BaseResource
{
    /**
     * List boards.
     *
     * List boards, newest first. Offset-paginated via `limit` + `skip`.
     *
     * @param array<string, mixed> $params Accepts: order (`asc`|`desc`), skip, limit.
     * @return Page<BoardRow>
     */
    public function list(array $params = []): Page
    {
        $query = self::pickQuery($params, ['order', 'skip', 'limit']);
        $env = $this->client->request('GET', '/v1/boards', $query !== [] ? ['query' => $query] : []);
        return Page::fromArray($env, fn (array $row): BoardRow => BoardRow::fromArray($row));
    }

    /**
     * Create or find board.
     *
     * Find-or-create the agent memory board for (`agent_id`, `domain`). Pass
     * `domain` for a built-in board (`tasks`/`leads`/`invoices`) or
     * `entity_kind` to open a new dynamic board (then `description` is
     * required). Returns the existing board if one exists.
     *
     * @param array<string, mixed> $params Accepts: domain, entity_kind, name,
     *   description, agent_id, memory_retention_days.
     */
    public function create(array $params = []): BoardDetail
    {
        $env = $this->client->request('POST', '/v1/boards', ['body' => $params]);
        return BoardDetail::fromArray(self::unwrap($env));
    }

    /**
     * Get board.
     *
     * Retrieve a board with its columns, options and item count.
     */
    public function retrieve(string $id): BoardDetail
    {
        $env = $this->client->request('GET', '/v1/boards/' . rawurlencode($id));
        return BoardDetail::fromArray(self::unwrap($env));
    }

    /**
     * List board items.
     *
     * List a board's items (tasks). Pass `active_only` to return only items
     * updated within the retention window whose status is non-terminal.
     * Offset-paginated via `limit` + `skip`.
     *
     * @param array<string, mixed> $params Accepts: status (label), order
     *   (`asc`|`desc`), active_only, updated_since, skip, limit.
     * @return Page<BoardItem>
     */
    public function listItems(string $boardId, array $params = []): Page
    {
        $query = self::pickQuery($params, ['status', 'order', 'active_only', 'updated_since', 'skip', 'limit']);
        $env = $this->client->request(
            'GET',
            '/v1/boards/' . rawurlencode($boardId) . '/items',
            $query !== [] ? ['query' => $query] : [],
        );
        return Page::fromArray($env, fn (array $row): BoardItem => BoardItem::fromArray($row));
    }

    /**
     * Create board item.
     *
     * Create one item. `status`/`priority` are given as labels and resolved
     * to the board's options server-side.
     *
     * @param array<string, mixed> $params Required: `text`. Accepts: status,
     *   priority, due_date, cells, source, confidence, allow_duplicate.
     */
    public function createItem(string $boardId, array $params): BoardItem
    {
        $env = $this->client->request(
            'POST',
            '/v1/boards/' . rawurlencode($boardId) . '/items',
            ['body' => $params],
        );
        return BoardItem::fromArray(self::unwrap($env));
    }

    /**
     * Update board item.
     *
     * Update cells, soft-delete (`deleted: true`) or restore
     * (`deleted: false`) an item. `note` is recorded in the item history.
     *
     * @param array<string, mixed> $params Accepts: text, status, priority,
     *   due_date, cells, source, confidence, deleted, note.
     */
    public function updateItem(string $boardId, string $itemId, array $params): BoardItem
    {
        $env = $this->client->request(
            'PATCH',
            '/v1/boards/' . rawurlencode($boardId) . '/items/' . rawurlencode($itemId),
            ['body' => $params],
        );
        return BoardItem::fromArray(self::unwrap($env));
    }

    /**
     * Bulk upsert board items.
     *
     * Create or merge many items in one call. Each item is fingerprinted
     * and matched against the board's existing items.
     *
     * @param array<string, mixed> $params Required: `items` (non-empty list).
     *   Accepts: mode (`upsert`).
     */
    public function bulkCreateItems(string $boardId, array $params): BulkItemsResponse
    {
        $env = $this->client->request(
            'POST',
            '/v1/boards/' . rawurlencode($boardId) . '/items/bulk',
            ['body' => $params],
        );
        return BulkItemsResponse::fromArray(self::unwrap($env));
    }

    /**
     * Find similar board items.
     *
     * Server-side dedupe: match each candidate text against the board's
     * items, returning match pairs and the misses.
     *
     * @param array<string, mixed> $params Required: `candidates` (non-empty
     *   list of `{text}` objects).
     */
    public function findSimilar(string $boardId, array $params): FindSimilarResponse
    {
        $env = $this->client->request(
            'POST',
            '/v1/boards/' . rawurlencode($boardId) . '/items/find-similar',
            ['body' => $params],
        );
        return FindSimilarResponse::fromArray(self::unwrap($env));
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
