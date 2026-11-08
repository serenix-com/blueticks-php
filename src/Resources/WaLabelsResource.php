<?php

declare(strict_types=1);

namespace Blueticks\Resources;

use Blueticks\BaseResource;
use Blueticks\Errors\ValidationError;
use Blueticks\Types\WaLabel;

/**
 * The account's native WhatsApp Business labels.
 */
final class WaLabelsResource extends BaseResource
{
    /**
     * List labels.
     *
     * List the native WhatsApp Business labels defined on the connected account.
     *
     * @return list<WaLabel>
     */
    public function list(): array
    {
        $env = $this->client->request('GET', '/v1/wa-labels');
        $out = [];
        foreach (self::unwrapList($env) as $row) {
            $out[] = WaLabel::fromArray($row);
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
