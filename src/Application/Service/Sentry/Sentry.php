<?php

declare(strict_types=1);

namespace App\Application\Service\Sentry;

use Sentry\State\HubInterface;
use Throwable;

class Sentry
{
    public function __construct(private readonly HubInterface $hub)
    {
    }

    public function capture(Throwable $exception): void
    {
        $this->hub->captureException($exception);
    }
}
