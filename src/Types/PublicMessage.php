<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * A message read from a chat's history — returned by `GET /v1/messages`
 * and `GET /v1/messages/{waMessageKey}`.
 */
final class PublicMessage
{
    public const SOURCES = ['vault', 'backup'];

    public function __construct(
        public readonly WaMessageKey $waMessageKey,
        public readonly string $chatId,
        public readonly string $from,
        public readonly ?string $author,
        public readonly ?string $senderName,
        public readonly ?string $timestamp,
        public readonly ?string $text,
        public readonly string $type,
        public readonly bool $fromMe,
        public readonly ?int $ack,
        public readonly ?string $mediaUrl,
        public readonly ?string $filename,
        public readonly ?LinkPreview $linkPreview,
        public readonly ?PublicQuotedMessage $quotedMessage,
        public readonly ?string $source = null,
        public readonly ?string $secret = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        if (!array_key_exists('waMessageKey', $data) || !is_array($data['waMessageKey'])) {
            throw new ValidationError(
                message: "Missing or non-object field 'waMessageKey' in PublicMessage response",
            );
        }
        /** @var array<string, mixed> $keyData */
        $keyData = $data['waMessageKey'];
        self::assertString($data, 'chatId');
        self::assertString($data, 'from');
        self::assertStringOrNull($data, 'author');
        self::assertStringOrNull($data, 'senderName');
        self::assertStringOrNull($data, 'timestamp');
        self::assertStringOrNull($data, 'text');
        self::assertString($data, 'type');
        if (!array_key_exists('fromMe', $data) || !is_bool($data['fromMe'])) {
            throw new ValidationError(message: "Missing or non-bool field 'fromMe' in PublicMessage response");
        }
        if (array_key_exists('ack', $data) && $data['ack'] !== null && !is_int($data['ack'])) {
            throw new ValidationError(message: "Field 'ack' must be int or null in PublicMessage response");
        }
        self::assertStringOrNull($data, 'mediaUrl');
        self::assertStringOrNull($data, 'filename');
        self::assertStringOrNull($data, 'secret');
        if (
            array_key_exists('source', $data)
            && $data['source'] !== null
            && (!is_string($data['source']) || !in_array($data['source'], self::SOURCES, true))
        ) {
            throw new ValidationError(
                message: "Field 'source' must be one of " . implode('/', self::SOURCES) . " in PublicMessage response",
            );
        }

        $linkPreview = null;
        if (array_key_exists('linkPreview', $data) && $data['linkPreview'] !== null) {
            if (!is_array($data['linkPreview'])) {
                throw new ValidationError(message: "Field 'linkPreview' must be an object in PublicMessage response");
            }
            /** @var array<string, mixed> $lp */
            $lp = $data['linkPreview'];
            $linkPreview = LinkPreview::fromArray($lp);
        }

        $quotedMessage = null;
        if (array_key_exists('quotedMessage', $data) && $data['quotedMessage'] !== null) {
            if (!is_array($data['quotedMessage'])) {
                throw new ValidationError(
                    message: "Field 'quotedMessage' must be an object in PublicMessage response",
                );
            }
            /** @var array<string, mixed> $qm */
            $qm = $data['quotedMessage'];
            $quotedMessage = PublicQuotedMessage::fromArray($qm);
        }

        return new self(
            waMessageKey: WaMessageKey::fromArray($keyData),
            chatId: $data['chatId'],
            from: $data['from'],
            author: $data['author'] ?? null,
            senderName: $data['senderName'] ?? null,
            timestamp: $data['timestamp'] ?? null,
            text: $data['text'] ?? null,
            type: $data['type'],
            fromMe: $data['fromMe'],
            ack: $data['ack'] ?? null,
            mediaUrl: $data['mediaUrl'] ?? null,
            filename: $data['filename'] ?? null,
            linkPreview: $linkPreview,
            quotedMessage: $quotedMessage,
            source: $data['source'] ?? null,
            secret: $data['secret'] ?? null,
        );
    }

    /** @param array<string, mixed> $data */
    private static function assertString(array $data, string $key): void
    {
        if (!array_key_exists($key, $data) || !is_string($data[$key])) {
            throw new ValidationError(message: "Missing or non-string field '{$key}' in PublicMessage response");
        }
    }

    /** @param array<string, mixed> $data */
    private static function assertStringOrNull(array $data, string $key): void
    {
        if (array_key_exists($key, $data) && $data[$key] !== null && !is_string($data[$key])) {
            throw new ValidationError(message: "Field '{$key}' must be string or null in PublicMessage response");
        }
    }
}
