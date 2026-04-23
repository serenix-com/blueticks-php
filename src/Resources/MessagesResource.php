<?php

declare(strict_types=1);

namespace Blueticks\Resources;

use Blueticks\BaseResource;
use Blueticks\Types\Message;

final class MessagesResource extends BaseResource
{
    /**
     * Send (or schedule) a message.
     *
     * @param array<string, mixed> $opts Accepts: text, media_url, media_caption, send_at, from, idempotency_key
     */
    public function send(string $to, array $opts = []): Message
    {
        $body = ['to' => $to];
        foreach (['text', 'media_url', 'media_caption', 'send_at', 'from'] as $k) {
            if (array_key_exists($k, $opts)) {
                $body[$k] = $opts[$k];
            }
        }

        $requestOpts = ['body' => $body];
        if (isset($opts['idempotency_key']) && is_string($opts['idempotency_key'])) {
            $requestOpts['headers'] = ['Idempotency-Key' => $opts['idempotency_key']];
        }

        $raw = $this->client->request('POST', '/v1/messages', $requestOpts);
        return Message::fromArray($raw);
    }

    public function get(string $id): Message
    {
        $raw = $this->client->request('GET', "/v1/messages/{$id}");
        return Message::fromArray($raw);
    }
}
