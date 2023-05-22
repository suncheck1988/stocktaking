<?php

declare(strict_types=1);

use App\Application\Service\CommandBus\ClassNameExtractor;
use League\Tactician\CommandBus;
use League\Tactician\Handler\CommandHandlerMiddleware;
use League\Tactician\Handler\Locator\CallableLocator;
use League\Tactician\Handler\MethodNameInflector\HandleInflector;
use Psr\Container\ContainerInterface;

return [
    CommandBus::class => static function (ContainerInterface $container): CommandBus {
        $commandHandlerMiddleware = new CommandHandlerMiddleware(
            new ClassNameExtractor(),
            new CallableLocator([$container, 'get']),
            new HandleInflector()
        );

        return new CommandBus([$commandHandlerMiddleware]);
    },
];
