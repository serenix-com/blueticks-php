<?php

declare(strict_types=1);

namespace Blueticks\Resources;

use Blueticks\BaseResource;
use Blueticks\Errors\ValidationError;
use Blueticks\Types\CreateUploadResponse;

/**
 * Pre-signed media uploads for media sends.
 *
 * Create an upload, PUT the file bytes to the returned `uploadUrl`, then pass
 * the returned `fileId` as `mediaFileId` on a media message.
 */
final class UploadsResource extends BaseResource
{
    /**
     * Create upload.
     *
     * Reserve an upload slot and get a pre-signed URL for the file bytes.
     *
     * @param array<string, mixed> $params Accepts: contentType (required MIME
     *   type), sizeBytes (optional, checked against the size limit up front),
     *   filename (optional attachment name).
     */
    public function create(array $params): CreateUploadResponse
    {
        $body = [];
        foreach (['contentType', 'sizeBytes', 'filename'] as $k) {
            if (array_key_exists($k, $params) && $params[$k] !== null) {
                $body[$k] = $params[$k];
            }
        }
        $env = $this->client->request('POST', '/v1/uploads', ['body' => $body]);
        return CreateUploadResponse::fromArray(self::unwrap($env));
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
