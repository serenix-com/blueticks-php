<?php

declare(strict_types=1);

namespace Blueticks\Resources;

use Blueticks\BaseResource;
use Blueticks\Errors\ValidationError;
use Blueticks\Types\Engine;

final class EnginesResource extends BaseResource
{
    /**
     * List engines.
     *
     * List the WhatsApp engines connected to this workspace — one entry per
     * online engine. Empty when no engine is paired.
     *
     * @return list<Engine>
     */
    public function list(): array
    {
        $env = $this->client->request('GET', '/v1/engines');
        $out = [];
        foreach (self::unwrapList($env) as $row) {
            $out[] = Engine::fromArray($row);
        }
        return $out;
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
