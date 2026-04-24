<?php

declare(strict_types=1);

namespace Blueticks\Resources;

use Blueticks\BaseResource;

final class UtilsResource extends BaseResource
{
    /** @return array<string, mixed> */
    public function validatePhone(string $phoneOrChatId): array
    {
        return $this->client->request(
            'POST',
            '/v1/utils/validate_phone',
            ['body' => ['phone_or_chat_id' => $phoneOrChatId]],
        );
    }

    /** @return array<string, mixed> */
    public function linkPreview(string $url): array
    {
        return $this->client->request(
            'GET',
            '/v1/utils/link_preview',
            ['query' => ['url' => $url]],
        );
    }
}
