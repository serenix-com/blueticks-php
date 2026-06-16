<?php

declare(strict_types=1);

namespace Blueticks\Types;

/**
 * Marker for the discriminated request body accepted by
 * `POST /v1/messages/{chatId}` (spec schema `SendInChatRequest`).
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
 * path) and `sendAt` (this endpoint is fire-and-forget).
 *
 * Variant fields (in addition to `type`), flat camelCase keys:
 *   - text:  `text` (string), optional `linkPreview`, `from`, `replyTo`,
 *            `mentions`.
 *   - media: `mediaUrl` OR `mediaBase64`, optional `mediaKind`,
 *            `mediaCaption`, `mediaFilename`, `from`, `replyTo`, `mentions`.
 *   - poll:  `pollQuestion`, `pollOptions[]`, optional `pollAllowMultiple`,
 *            `from`, `replyTo`, `mentions`.
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
