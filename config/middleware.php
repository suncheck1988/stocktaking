<?php

declare(strict_types=1);

use App\UI\Http\Middleware\AuthMiddleware;
use App\UI\Http\Middleware\ClearEmptyInput;
use App\UI\Http\Middleware\DdosMiddleware;
use App\UI\Http\Middleware\TranslatorLocale;
use Slim\App;
use Slim\Middleware\ErrorMiddleware;

return static function (App $app): void {
    $app->add(ClearEmptyInput::class);
    $app->add(TranslatorLocale::class);
    $app->addBodyParsingMiddleware();
    $app->add(ErrorMiddleware::class);
    $app->add(AuthMiddleware::class);
    $app->add(DdosMiddleware::class);
};
