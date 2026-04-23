<?php

declare(strict_types=1);

namespace Blueticks\Tests;

use Blueticks\Errors\APIConnectionError;
use Blueticks\Errors\APIError;
use Blueticks\Errors\AuthenticationError;
use Blueticks\Errors\BadRequestError;
use Blueticks\Errors\BluetickError;
use Blueticks\Errors\NotFoundError;
use Blueticks\Errors\PermissionDeniedError;
use Blueticks\Errors\RateLimitError;
use Blueticks\Errors\ValidationError;
use PHPUnit\Framework\TestCase;

final class ErrorsTest extends TestCase
{
    public function testBluetickErrorCarriesAllFields(): void
    {
        $err = new BluetickError(
            statusCode: 401,
            code: 'authentication_required',
            message: 'bad key',
            requestId: 'req_xyz',
        );

        self::assertSame(401, $err->statusCode);
        self::assertSame('authentication_required', $err->code);
        self::assertSame('bad key', $err->getMessage());
        self::assertSame('req_xyz', $err->requestId);
        self::assertNull($err->response);
    }

    public function testToStringFormatsParityWithPythonAndNode(): void
    {
        $err = new BluetickError(
            statusCode: 401,
            code: 'authentication_required',
            message: 'bad key',
            requestId: 'req_xyz',
        );
        self::assertSame(
            '401 authentication_required: bad key (request_id=req_xyz)',
            (string) $err,
        );
    }

    public function testToStringWithoutRequestIdOmitsTrailer(): void
    {
        $err = new BluetickError(
            statusCode: 500,
            code: 'internal_error',
            message: 'boom',
        );
        self::assertSame('500 internal_error: boom', (string) $err);
    }

    public function testToStringWithoutStatusOrCodeFallsBackToMessage(): void
    {
        $err = new BluetickError(message: 'network died');
        self::assertSame('network died', (string) $err);
    }

    /**
     * @dataProvider subclassCases
     * @param class-string<BluetickError> $class
     */
    public function testSubclassesExtendBluetickError(string $class): void
    {
        self::assertTrue(is_subclass_of($class, BluetickError::class), "{$class} must extend BluetickError");
    }

    /** @return array<string, array{0: class-string<BluetickError>}> */
    public static function subclassCases(): array
    {
        return [
            'auth'         => [AuthenticationError::class],
            'permission'   => [PermissionDeniedError::class],
            'not_found'    => [NotFoundError::class],
            'bad_request'  => [BadRequestError::class],
            'rate_limit'   => [RateLimitError::class],
            'api_error'    => [APIError::class],
            'api_conn'     => [APIConnectionError::class],
            'validation'   => [ValidationError::class],
        ];
    }

    public function testRateLimitErrorCarriesRetryAfter(): void
    {
        $err = new RateLimitError(
            statusCode: 429,
            code: 'rate_limited',
            message: 'slow down',
            requestId: 'req_rl',
            retryAfter: 12,
        );
        self::assertSame(12, $err->retryAfter);
        self::assertSame(429, $err->statusCode);
        self::assertSame('rate_limited', $err->code);
    }

    public function testCatchableByParent(): void
    {
        $this->expectException(BluetickError::class);
        throw new AuthenticationError(
            statusCode: 401,
            code: 'authentication_required',
            message: 'bad key',
        );
    }
}
