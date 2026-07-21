<?php

declare(strict_types=1);

namespace Blueticks\Types;

/**
 * Marker for the discriminated request body accepted by
 * `POST /v1/chats/{chat_id}/messages` (spec schema `SendInChatRequest`).
 *
 * The PHP SDK passes request bodies as associative arrays — see
 * `ChatsResource::sendMessage()`. This class exposes the discriminator
 * values as constants so callers don't hand-type the magic strings:
 *
 *   $bt->chats->sendMessage('1234@c.us', [
 *       'type' => SendInChatRequest::TYPE_TEXT,
 *       'text' => 'hi',
 *   ]);
 *
 * The shape mirrors `SendMessageRequest` minus `to` (taken from the URL
 * path) and `send_at` (this endpoint is fire-and-forget).
 *
 * Variant fields (in addition to `type`):
 *   - text:  `text` (string), optional `url`, `link_preview`, `from`,
 *            `reply_to`, `mentions`.
 *   - media: `media` (object: `url` OR `data_base64`, optional `kind`,
 *            `caption`, `filename`), optional `from`, `reply_to`, `mentions`.
 *   - poll:  `poll` (object: `question`, `options[]`, optional
 *            `allow_multiple`), optional `from`, `reply_to`, `mentions`.
 */
final class SendInChatRequest
{
    public const TYPE_TEXT = 'text';
    public const TYPE_MEDIA = 'media';
    public const TYPE_POLL = 'poll';

    /** @var list<string> */
    public const TYPES = [
        self::TYPE_TEXT,
        self::TYPE_MEDIA,
        self::TYPE_POLL,
    ];
}
