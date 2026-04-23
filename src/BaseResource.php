<?php

declare(strict_types=1);

namespace Blueticks;

abstract class BaseResource
{
    public function __construct(protected readonly Blueticks $client)
    {
    }
}
