<?php

declare(strict_types=1);

namespace Blueticks\Resources;

use Blueticks\BaseResource;
use Blueticks\Errors\ValidationError;
use Blueticks\Types\Identity;

/**
 * Resolve WhatsApp JIDs to canonical phone-form identities.
 */
final class IdentityResource extends BaseResource
{
    /**
     * Resolve identities (batch).
     *
     * Resolve up to 100 WhatsApp JIDs to their canonical phone-form
     * identities in one call. Order-preserving.
     *
     * @param list<string> $ids
     * @return list<Identity>
     */
    public function resolve(array $ids): array
    {
        $env = $this->client->request('POST', '/v1/identity/resolve', ['body' => ['ids' => $ids]]);
        $data = $env['data'] ?? null;
        if (!is_array($data)) {
            throw new ValidationError(message: 'Malformed API response: missing "data" array');
        }
        $out = [];
        foreach ($data as $row) {
            if (!is_array($row)) {
                throw new ValidationError(message: 'Malformed API response: expected an array of objects');
            }
            /** @var array<string, mixed> $row */
            $out[] = Identity::fromArray($row);
        }
        return $out;
    }

    /**
     * Resolve an identity.
     *
     * Resolve a single WhatsApp JID (`...@lid`, `...@c.us`,
     * `...@s.whatsapp.net` or `...@g.us`) to its canonical phone-form
     * identity.
     */
    public function retrieve(string $id): Identity
    {
        $env = $this->client->request('GET', '/v1/identity/' . rawurlencode($id));
        return Identity::fromArray(self::unwrap($env));
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
