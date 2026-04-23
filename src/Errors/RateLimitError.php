<?php

declare(strict_types=1);

namespace Blueticks\Errors;

use Psr\Http\Message\ResponseInterface;

class RateLimitError extends BluetickError
{
    public function __construct(
        ?int $statusCode = null,
        ?string $code = null,
        string $message = '',
        ?string $requestId = null,
        ?ResponseInterface $response = null,
        public readonly ?int $retryAfter = null,
    ) {
        parent::__construct($statusCode, $code, $message, $requestId, $response);
    }
}
