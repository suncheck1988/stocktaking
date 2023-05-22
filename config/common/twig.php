<?php

declare(strict_types=1);

use Slim\Views\Twig;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

return [
    Twig::class => static function (): Twig {
        return Twig::create(
            __DIR__ . '/../../templates',
            [
                'cache' => __DIR__ . '/../../var/cache/twig',
                'debug' => getenv('APP_ENV') === 'test' || getenv('APP_ENV') === 'dev',
            ]
        );
    },

    Environment::class => static function (): Environment {
        $loader = new FilesystemLoader(__DIR__ . '/../../templates');
        return new Environment($loader, [
            __DIR__ . '/../../var/cache/twig',
        ]);
    },
];
