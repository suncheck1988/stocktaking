<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

/** @var ContainerInterface $container */
$container = require __DIR__ . '/../config/container.php';

if (file_exists(__DIR__ . '/../.env')) {
    (new Dotenv('APP_ENV'))->usePutenv()->load(__DIR__ . '/../.env');
}

$app = (require __DIR__ . '/../config/app.php')($container);
$app->run();
