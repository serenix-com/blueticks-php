<?php

declare(strict_types=1);

namespace Blueticks\Resources;

use Blueticks\BaseResource;
use Blueticks\Errors\ValidationError;
use Blueticks\Types\LatestChatsResponse;

/**
 * Latest chats with their most recent messages in one call.
 */
final class LatestChatsResource extends BaseResource
{
    /**
     * Get latest chats and messages.
     *
     * Returns the most-recent non-archived chats (pinned first), each with
     * its latest messages (newest first), in a single call.
     *
     * @param array<string, mixed> $params Accepts: numberOfChats (default 50),
     *   numberOfMessages (default 20).
     */
    public function list(array $params = []): LatestChatsResponse
    {
        $query = self::pickQuery($params, ['numberOfChats', 'numberOfMessages']);
        $env = $this->client->request('GET', '/v1/latest-chats', $query !== [] ? ['query' => $query] : []);
        return LatestChatsResponse::fromArray(self::unwrap($env));
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
