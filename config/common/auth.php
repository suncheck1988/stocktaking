<?php

declare(strict_types=1);

use App\Auth\Service\AuthTokenManager;

return [
    AuthTokenManager::class => static fn (): AuthTokenManager => new AuthTokenManager((string)getenv('APP_AUTH_SECRET_KEY')),
];
