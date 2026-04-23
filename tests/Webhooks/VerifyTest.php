<?php

declare(strict_types=1);

namespace Blueticks\Tests\Webhooks;

use Blueticks\Errors\WebhookVerificationError;
use Blueticks\Types\WebhookEvent;
use PHPUnit\Framework\TestCase;

use function Blueticks\Webhooks\verify;

final class VerifyTest extends TestCase
{
    private const SECRET = 'whsec_test';

    /** @return array{0: string, 1: array<string, string>} */
    private static function buildSigned(string $payload, ?int $ts = null, ?string $secret = null): array
    {
        $ts ??= time();
        $secret ??= self::SECRET;
        $sig = hash_hmac('sha256', "{$ts}.{$payload}", $secret);
        return [$payload, [
            'Blueticks-Webhook-Timestamp' => (string) $ts,
            'Blueticks-Webhook-Signature' => "v1={$sig}",
        ]];
    }

    /** @return array<string, mixed> */
    private static function eventBody(): array
    {
        return [
            'id' => 'evt_1',
            'type' => 'message.delivered',
            'created_at' => '2026-04-23T10:00:00Z',
            'data' => ['message_id' => 'msg_1'],
        ];
    }

    public function testValidSignature(): void
    {
        $payload = (string) json_encode(self::eventBody());
        [$body, $headers] = self::buildSigned($payload);
        $ev = verify($body, $headers, self::SECRET);
        self::assertInstanceOf(WebhookEvent::class, $ev);
        self::assertSame('evt_1', $ev->id);
        self::assertSame('message.delivered', $ev->type);
    }

    public function testExpiredTimestamp(): void
    {
        $payload = (string) json_encode(self::eventBody());
        [$body, $headers] = self::buildSigned($payload, ts: time() - 10_000);
        $this->expectException(WebhookVerificationError::class);
        $this->expectExceptionMessage('expired timestamp');
        verify($body, $headers, self::SECRET);
    }

    public function testTamperedPayload(): void
    {
        $payload = (string) json_encode(self::eventBody());
        [, $headers] = self::buildSigned($payload);
        $tampered = $payload . ' ';
        $this->expectException(WebhookVerificationError::class);
        verify($tampered, $headers, self::SECRET);
    }

    public function testWrongSecret(): void
    {
        $payload = (string) json_encode(self::eventBody());
        [$body, $headers] = self::buildSigned($payload);
        $this->expectException(WebhookVerificationError::class);
        verify($body, $headers, 'whsec_wrong');
    }

    public function testMissingHeader(): void
    {
        $payload = (string) json_encode(self::eventBody());
        $this->expectException(WebhookVerificationError::class);
        $this->expectExceptionMessage('missing required headers');
        verify($payload, [], self::SECRET);
    }

    public function testCaseInsensitiveHeaders(): void
    {
        $payload = (string) json_encode(self::eventBody());
        [$body, $headers] = self::buildSigned($payload);
        $lcHeaders = [];
        foreach ($headers as $k => $v) {
            $lcHeaders[strtolower($k)] = $v;
        }
        $ev = verify($body, $lcHeaders, self::SECRET);
        self::assertSame('evt_1', $ev->id);
    }

    public function testHeaderArrayValue(): void
    {
        $payload = (string) json_encode(self::eventBody());
        [$body, $headers] = self::buildSigned($payload);
        /** @var array<string, string|list<string>> $wrapped */
        $wrapped = [];
        foreach ($headers as $k => $v) {
            $wrapped[$k] = [$v];
        }
        $ev = verify($body, $wrapped, self::SECRET);
        self::assertSame('evt_1', $ev->id);
    }

    public function testMissingV1Scheme(): void
    {
        $payload = (string) json_encode(self::eventBody());
        $ts = time();
        $headers = [
            'Blueticks-Webhook-Timestamp' => (string) $ts,
            'Blueticks-Webhook-Signature' => 'v0=abc',
        ];
        $this->expectException(WebhookVerificationError::class);
        $this->expectExceptionMessage('missing v1 scheme');
        verify($payload, $headers, self::SECRET);
    }

    public function testNonJsonPayloadThrows(): void
    {
        $payload = 'not json';
        [$body, $headers] = self::buildSigned($payload);
        $this->expectException(WebhookVerificationError::class);
        verify($body, $headers, self::SECRET);
    }
}
