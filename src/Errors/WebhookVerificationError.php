<?php

declare(strict_types=1);

namespace Blueticks\Errors;

/**
 * Raised by Blueticks\Webhooks\verify() when signature verification fails.
 */
final class WebhookVerificationError extends BluetickError
{
    public function __construct(string $message)
    {
        parent::__construct(
            statusCode: null,
            code: 'webhook_verification_failed',
            message: $message,
        );
    }
}
