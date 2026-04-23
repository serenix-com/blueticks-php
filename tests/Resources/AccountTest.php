<?php

declare(strict_types=1);

namespace Blueticks\Tests\Resources;

use Blueticks\Blueticks;
use Blueticks\Errors\AuthenticationError;
use Blueticks\Tests\Helpers\MockTransport;
use Blueticks\Types\Account;
use PHPUnit\Framework\TestCase;

final class AccountTest extends TestCase
{
    private function client(MockTransport $mock): Blueticks
    {
        return new Blueticks([
            'apiKey'         => 'bt_test_x',
            'baseUrl'        => 'https://api.blueticks.test',
            'httpClient'     => $mock->client(),
            'requestFactory' => $mock->factories(),
            'streamFactory'  => $mock->factories(),
            'retryBaseMs'    => 0,
            'retryCapMs'     => 0,
            'sleeper'        => function (int $_ms): void {
            },
        ]);
    }

    public function testRetrieveHappyPath(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(200, [
            'id' => 'acct_abc123',
            'name' => 'Acme Corp',
            'timezone' => 'Europe/Berlin',
            'created_at' => '2026-04-23T10:00:00Z',
        ]);

        $account = $this->client($mock)->account->retrieve();

        self::assertInstanceOf(Account::class, $account);
        self::assertSame('Acme Corp', $account->name);
        self::assertSame('Europe/Berlin', $account->timezone);

        $request = $mock->requests()[0];
        self::assertSame('GET', $request->getMethod());
        self::assertSame('https://api.blueticks.test/v1/account', (string) $request->getUri());
    }

    public function testRetrieve401MapsToAuthenticationError(): void
    {
        $mock = new MockTransport();
        $mock->enqueueJson(401, [
            'error' => [
                'code' => 'authentication_required',
                'message' => 'bad key',
                'request_id' => 'req_zzz',
            ],
        ]);

        try {
            $this->client($mock)->account->retrieve();
            self::fail('Expected AuthenticationError');
        } catch (AuthenticationError $e) {
            self::assertSame(401, $e->statusCode);
            self::assertSame('authentication_required', $e->code);
            self::assertSame('req_zzz', $e->requestId);
        }
    }
}
