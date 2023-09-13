<?php

declare(strict_types=1);

namespace App\Application\Dto;

use DI\Container;
use DI\ContainerBuilder;
use Exception;

abstract class AbstractJsonResponse
{
    protected static ?Container $container = null;

    /**
     * @throws Exception
     */
    protected static function getContainer(): Container
    {
        if (static::$container === null) {
            $containerBuilder = static::configureContainer();
            static::$container = $containerBuilder->build();
        }

        return static::$container;
    }

    protected static function configureContainer(): ContainerBuilder
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions(require __DIR__ . '/../../../config/dependencies.php');

        return $containerBuilder;
    }
}
