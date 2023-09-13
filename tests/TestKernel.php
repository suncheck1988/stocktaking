<?php

declare(strict_types=1);

namespace Test;

use Closure;
use DI\Container;
use DI\ContainerBuilder;
use Exception;

class TestKernel
{
    protected static ?Container $container = null;

    protected static ?Closure $routes = null;

    /**
     * @throws Exception
     */
    public static function getContainer(): Container
    {
        if (static::$container === null) {
            $containerBuilder = static::configureContainer();
            static::$container = $containerBuilder->build();
        }

        return static::$container;
    }

    public static function getRoutes(): Closure
    {
        if (self::$routes === null) {
            self::$routes = require __DIR__ . '/../config/routes.php';
        }

        return self::$routes;
    }

    protected static function configureContainer(): ContainerBuilder
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions(require __DIR__ . '/../config/dependencies.php');

        return $containerBuilder;
    }
}