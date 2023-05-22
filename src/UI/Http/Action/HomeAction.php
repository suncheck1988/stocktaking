<?php

declare(strict_types=1);

namespace App\UI\Http\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class HomeAction extends AbstractAction
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->asJson([]);
    }
}
