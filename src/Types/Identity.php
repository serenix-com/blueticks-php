<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

/**
 * A WhatsApp identity resolved to its canonical phone-form (`@lid` / `@c.us` /
 * `@s.whatsapp.net` / `@g.us`).
 */
final class Identity
{
    public const TYPES = ['user', 'group', 'unknown'];
    public const SOURCES = ['engine', 'cache', 'unknown'];

    /**
     * @param list<string> $aliases
     */
    public function __construct(
        public readonly string $input,
        public readonly bool $resolved,
        public readonly ?string $canonical,
        public readonly ?string $phone,
        public readonly ?string $lid,
        public readonly array $aliases,
        public readonly string $type,
        public readonly string $source,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            input: self::reqString($data, 'input'),
            resolved: self::reqBool($data, 'resolved'),
            canonical: self::optString($data, 'canonical'),
            phone: self::optString($data, 'phone'),
            lid: self::optString($data, 'lid'),
            aliases: self::reqStringList($data, 'aliases'),
            type: self::reqEnum($data, 'type', self::TYPES),
            source: self::reqEnum($data, 'source', self::SOURCES),
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function optString(array $data, string $key): ?string
    {
        if (!array_key_exists($key, $data) || $data[$key] === null) {
            return null;
        }
        if (!is_string($data[$key])) {
            throw new ValidationError(
                message: "Field '{$key}' must be string or null in Identity response",
            );
        }
        return $data[$key];
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function reqBool(array $data, string $key): bool
    {
        if (!array_key_exists($key, $data) || !is_bool($data[$key])) {
            throw new ValidationError(
                message: "Missing or non-bool field '{$key}' in Identity response",
            );
        }
        return $data[$key];
    }

    /**
     * @param array<string, mixed> $data
     * @param list<string> $allowed
     */
    private static function reqEnum(array $data, string $key, array $allowed): string
    {
        if (!array_key_exists($key, $data) || !is_string($data[$key]) || !in_array($data[$key], $allowed, true)) {
            throw new ValidationError(
                message: "Field '{$key}' must be one of " . implode('/', $allowed) . " in Identity response",
            );
        }
        return $data[$key];
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function reqString(array $data, string $key): string
    {
        if (!array_key_exists($key, $data) || !is_string($data[$key])) {
            throw new ValidationError(
                message: "Missing or non-string field '{$key}' in Identity response",
            );
        }
        return $data[$key];
    }

    /**
     * @param array<string, mixed> $data
     * @return list<string>
     */
    private static function reqStringList(array $data, string $key): array
    {
        if (!array_key_exists($key, $data) || !is_array($data[$key])) {
            throw new ValidationError(
                message: "Missing or non-array field '{$key}' in Identity response",
            );
        }
        $out = [];
        foreach ($data[$key] as $item) {
            if (!is_string($item)) {
                throw new ValidationError(
                    message: "Field '{$key}' must contain only strings in Identity response",
                );
            }
            $out[] = $item;
        }
        return $out;
    }
}
