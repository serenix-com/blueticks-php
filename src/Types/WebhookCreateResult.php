<?php

declare(strict_types=1);

namespace Blueticks\Types;

final class WebhookCreateResult extends Webhook
{
    /**
     * @param list<string> $events
     */
    public function __construct(
        string $id,
        string $url,
        array $events,
        ?string $description,
        string $status,
        string $created_at,
        public readonly string $secret,
    ) {
        parent::__construct($id, $url, $events, $description, $status, $created_at);
    }

    /**
     * @param array<string, mixed> $raw
     */
    public static function fromArray(array $raw): self
    {
        self::assertString($raw, 'id');
        self::assertString($raw, 'url');
        self::assertStringList($raw, 'events');
        self::assertStringOrNull($raw, 'description');
        self::assertString($raw, 'status');
        self::assertString($raw, 'created_at');
        self::assertString($raw, 'secret');

        /** @var list<string> $events */
        $events = array_values($raw['events']);

        return new self(
            id: $raw['id'],
            url: $raw['url'],
            events: $events,
            description: $raw['description'],
            status: $raw['status'],
            created_at: $raw['created_at'],
            secret: $raw['secret'],
        );
    }
}
