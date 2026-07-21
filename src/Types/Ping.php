<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * Health and connectivity probe returned by `GET /v1/ping`.
 */
final class Ping
{
    /**
     * @param list<PingConnection> $whatsappConnections
     */
    public function __construct(
        public readonly string $api,
        public readonly string $accountId,
        public readonly array $whatsappConnections,
        public readonly ?string $message,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        if (!array_key_exists('api', $data) || $data['api'] !== 'ok') {
            throw new ValidationError(message: "Field 'api' must equal 'ok' in Ping response");
        }
        if (!array_key_exists('accountId', $data) || !is_string($data['accountId'])) {
            throw new ValidationError(message: "Missing or non-string field 'accountId' in Ping response");
        }
        if (!array_key_exists('whatsappConnections', $data) || !is_array($data['whatsappConnections'])) {
            throw new ValidationError(
                message: "Missing or non-array field 'whatsappConnections' in Ping response",
            );
        }
        if (array_key_exists('message', $data) && $data['message'] !== null && !is_string($data['message'])) {
            throw new ValidationError(message: "Field 'message' must be string or null in Ping response");
        }

        $connections = [];
        foreach ($data['whatsappConnections'] as $row) {
            if (!is_array($row)) {
                throw new ValidationError(
                    message: "Each entry of 'whatsappConnections' must be an object in Ping response",
                );
            }
            /** @var array<string, mixed> $row */
            $connections[] = PingConnection::fromArray($row);
        }

        return new self(
            api: $data['api'],
            accountId: $data['accountId'],
            whatsappConnections: $connections,
            message: $data['message'] ?? null,
        );
    }
}
