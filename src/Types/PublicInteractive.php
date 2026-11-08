<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * An interactive menu, click or reply (`interactive` on a message).
 *
 * `options` items are `{id, title, description?, section?}` objects, kept
 * as associative arrays.
 */
final class PublicInteractive
{
    public const KINDS = [
        'buttons', 'native_flow', 'buttons_response', 'list', 'list_response', 'template',
        'template_button_reply', 'interactive', 'interactive_response',
    ];

    /**
     * @param ?list<array<string, mixed>> $options
     */
    public function __construct(
        public readonly string $kind,
        public readonly ?array $options = null,
        public readonly ?string $selectedId = null,
        public readonly ?string $selectedTitle = null,
        public readonly ?string $quotedId = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        if (
            !array_key_exists('kind', $data)
            || !is_string($data['kind'])
            || !in_array($data['kind'], self::KINDS, true)
        ) {
            throw new ValidationError(message: "Missing or unknown field 'kind' in PublicInteractive response");
        }
        $options = null;
        if (array_key_exists('options', $data) && $data['options'] !== null) {
            if (!is_array($data['options'])) {
                throw new ValidationError(
                    message: "Field 'options' must be array or null in PublicInteractive response",
                );
            }
            $options = [];
            foreach ($data['options'] as $opt) {
                if (!is_array($opt)) {
                    throw new ValidationError(
                        message: "Field 'options' must contain objects in PublicInteractive response",
                    );
                }
                /** @var array<string, mixed> $opt */
                $options[] = $opt;
            }
        }
        foreach (['selectedId', 'selectedTitle', 'quotedId'] as $key) {
            if (array_key_exists($key, $data) && $data[$key] !== null && !is_string($data[$key])) {
                throw new ValidationError(
                    message: "Field '{$key}' must be string or null in PublicInteractive response",
                );
            }
        }

        return new self(
            kind: $data['kind'],
            options: $options,
            selectedId: isset($data['selectedId']) && is_string($data['selectedId']) ? $data['selectedId'] : null,
            selectedTitle: isset($data['selectedTitle']) && is_string($data['selectedTitle'])
                ? $data['selectedTitle']
                : null,
            quotedId: isset($data['quotedId']) && is_string($data['quotedId']) ? $data['quotedId'] : null,
        );
    }
}
