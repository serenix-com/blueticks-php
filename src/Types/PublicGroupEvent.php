<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * A normalized group notice (`groupEvent` on a message).
 */
final class PublicGroupEvent
{
    public const ACTIONS = [
        'join', 'add', 'leave', 'remove', 'promote', 'demote', 'subject', 'description',
        'picture', 'settings', 'create', 'modify', 'auto_add', 'join_request',
        'participants_resync', 'other',
    ];

    /**
     * @param ?list<string> $targets
     */
    public function __construct(
        public readonly string $action,
        public readonly ?string $actor = null,
        public readonly ?array $targets = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        if (
            !array_key_exists('action', $data)
            || !is_string($data['action'])
            || !in_array($data['action'], self::ACTIONS, true)
        ) {
            throw new ValidationError(message: "Missing or unknown field 'action' in PublicGroupEvent response");
        }
        if (array_key_exists('actor', $data) && $data['actor'] !== null && !is_string($data['actor'])) {
            throw new ValidationError(message: "Field 'actor' must be string or null in PublicGroupEvent response");
        }
        $targets = null;
        if (array_key_exists('targets', $data) && $data['targets'] !== null) {
            if (!is_array($data['targets'])) {
                throw new ValidationError(
                    message: "Field 'targets' must be array or null in PublicGroupEvent response",
                );
            }
            $targets = [];
            foreach ($data['targets'] as $t) {
                if (!is_string($t)) {
                    throw new ValidationError(
                        message: "Field 'targets' must contain strings in PublicGroupEvent response",
                    );
                }
                $targets[] = $t;
            }
        }

        return new self(
            action: $data['action'],
            actor: isset($data['actor']) && is_string($data['actor']) ? $data['actor'] : null,
            targets: $targets,
        );
    }
}
