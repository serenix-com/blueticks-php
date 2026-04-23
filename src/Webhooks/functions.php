<?php

declare(strict_types=1);

namespace Blueticks\Webhooks;

use Blueticks\Errors\WebhookVerificationError;
use Blueticks\Types\WebhookEvent;

const TOLERANCE_SECONDS = 300;

/**
 * Verify a Blueticks webhook signature and return the parsed event.
 *
 * @param array<string, string|list<string>> $headers
 *   Headers map; values can be string or list of strings. Lookup is case-insensitive.
 *
 * @throws WebhookVerificationError
 */
function verify(string $payload, array $headers, string $secret, int $tolerance = TOLERANCE_SECONDS): WebhookEvent
{
    $timestamp = _header($headers, 'Blueticks-Webhook-Timestamp');
    $signature = _header($headers, 'Blueticks-Webhook-Signature');
    if ($timestamp === null || $signature === null) {
        throw new WebhookVerificationError('missing required headers');
    }
    if (!ctype_digit($timestamp)) {
        throw new WebhookVerificationError('invalid timestamp');
    }
    $ts = (int) $timestamp;
    if (abs(time() - $ts) > $tolerance) {
        throw new WebhookVerificationError('expired timestamp');
    }

    $signed = "{$ts}.{$payload}";
    $expected = hash_hmac('sha256', $signed, $secret);

    $supplied = null;
    foreach (explode(',', $signature) as $p) {
        $p = trim($p);
        if (str_starts_with($p, 'v1=')) {
            $supplied = substr($p, 3);
            break;
        }
    }
    if ($supplied === null) {
        throw new WebhookVerificationError('invalid_signature: missing v1 scheme');
    }
    if (!hash_equals($expected, $supplied)) {
        throw new WebhookVerificationError('invalid_signature: mismatch');
    }

    $data = json_decode($payload, true);
    if (!is_array($data)) {
        throw new WebhookVerificationError('invalid_signature: payload not JSON object');
    }
    /** @var array<string, mixed> $data */
    return WebhookEvent::fromArray($data);
}

/**
 * @internal
 * @param array<string, string|list<string>> $headers
 */
function _header(array $headers, string $name): ?string
{
    if (isset($headers[$name])) {
        $v = $headers[$name];
        if (is_array($v)) {
            return isset($v[0]) ? (string) $v[0] : null;
        }
        return (string) $v;
    }
    $lower = strtolower($name);
    foreach ($headers as $k => $v) {
        if (strtolower((string) $k) === $lower) {
            if (is_array($v)) {
                return isset($v[0]) ? (string) $v[0] : null;
            }
            return (string) $v;
        }
    }
    return null;
}
