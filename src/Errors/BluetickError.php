<?php

declare(strict_types=1);

namespace Blueticks\Errors;

use Exception;
use Psr\Http\Message\ResponseInterface;

/**
 * Base exception for all Blueticks SDK errors.
 *
 * @property-read ?string $code  String error code (virtual; PHP's Exception::$code is an untyped int
 *                                so we cannot redeclare with a type — exposed via __get instead).
 */
class BluetickError extends Exception
{
    public readonly ?int $statusCode;

    /** @var ?string */
    private readonly ?string $errorCode;

    public readonly ?string $requestId;

    public readonly ?ResponseInterface $response;

    public function __construct(
        ?int $statusCode = null,
        ?string $code = null,
        string $message = '',
        ?string $requestId = null,
        ?ResponseInterface $response = null,
    ) {
        parent::__construct($message);
        $this->statusCode = $statusCode;
        $this->errorCode = $code;
        $this->requestId = $requestId;
        $this->response = $response;
    }

    /**
     * Exposes the string error code as a virtual public property ($err->code).
     *
     * @param string $name
     * @return ?string
     */
    public function __get(string $name): ?string
    {
        if ($name === 'code') {
            return $this->errorCode;
        }
        return null;
    }

    /**
     * @param string $name
     */
    public function __isset(string $name): bool
    {
        if ($name === 'code') {
            return $this->errorCode !== null;
        }
        return false;
    }

    public function __toString(): string
    {
        $prefixParts = [];
        if ($this->statusCode !== null) {
            $prefixParts[] = (string) $this->statusCode;
        }
        if ($this->errorCode !== null) {
            $prefixParts[] = $this->errorCode;
        }
        $prefix = implode(' ', $prefixParts);
        $body = $prefix !== '' ? $prefix . ': ' . $this->getMessage() : $this->getMessage();
        if ($this->requestId !== null) {
            $body .= ' (request_id=' . $this->requestId . ')';
        }
        return $body;
    }
}
